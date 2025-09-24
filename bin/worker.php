#!/usr/bin/env php
<?php

use Psr\Log\LoggerInterface;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

try {
    $container = require dirname(__DIR__, 1) . '/config/container.php';
    $logger = $container->get(LoggerInterface::class);
    $transport = $container->get('doctrine.messenger.transport');
    $handlers = $container->get('doctrine.messenger.handlers');

    // Инициализация блокировки
    $lockDir = dirname(__DIR__, 1) . '/var/lock';
    $lockFile = $lockDir . '/messenger_worker.lock';

    if (!is_dir($lockDir)) {
        mkdir($lockDir, 0775, true);
    }

    $fp = fopen($lockFile, 'w+');
    if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $logger->info('Worker already running');
        exit(0);
    }

    // Health-статус
    $healthFile = dirname(__DIR__, 1) . '/public/worker-status.json';
    $updateHealthStatus = function() use ($healthFile, $logger) {
        file_put_contents($healthFile, json_encode([
            'status' => 'running',
            'last_update' => date('c'),
            'memory' => memory_get_usage(true) / 1024 / 1024 . ' MB'
        ]));
    };

    // Конфигурация
    $config = [
        'max_messages' => 50,
        'max_lifetime' => 240,
        'memory_limit' => 100 * 1024 * 1024, // 100MB
        'batch_size' => 10,
    ];

    $logger->info('Worker started', [
        'pid' => getmypid(),
        'config' => $config
    ]);

    $processedCount = 0;
    $lastHealthUpdate = 0;
    $startTime = time();

    while ($processedCount < $config['max_messages']
        && memory_get_usage() < $config['memory_limit']
        && (time() - $startTime) < $config['max_lifetime']) {
        // Обновляем health-статус каждые 30 сек
        if (time() - $lastHealthUpdate > 30) {
            $updateHealthStatus();
            $lastHealthUpdate = time();
        }

        $envelopes = $transport->get($config['batch_size']);
        if (empty($envelopes)) {
            sleep(1);
            continue;
        }

        foreach ($envelopes as $envelope) {
            $message = $envelope->getMessage();
            $messageClass = get_class($message);

            if (!isset($handlers[$messageClass])) {
                $logger->error('Handler not found', ['message_class' => $messageClass]);
                continue;
            }

            try {
                $handlers[$messageClass]->handle($message);
                $transport->ack($envelope);
                $processedCount++;

                $logger->debug('Message processed', [
                    'message_class' => $messageClass,
                    'processed_count' => $processedCount
                ]);
            } catch (\Throwable $e) {
                $logger->error('Message processing failed', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    $logger->info('Worker finished', [
        'processed' => $processedCount,
        'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
    ]);

} catch (\Throwable $e) {
    $logger->critical('Worker fatal error', [
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit(1);
} finally {
    if (isset($fp)) {
        flock($fp, LOCK_UN);
        fclose($fp);
        @unlink($lockFile);
    }
    $updateHealthStatus();
}

exit(0);
