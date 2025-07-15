#!/usr/bin/env php

<?php

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';
$container = require dirname(__DIR__, 1) . '/config/container.php';
$transport = $container->get('doctrine.messenger.transport');
$handlers = $container->get('doctrine.messenger.handlers');
$logger = $container->get(LoggerInterface::class);

$startTime = time();
$processedCount = 0;
$config = [
    'memory_limit' => 100 * 1024 * 1024, // 100MB
    'max_runtime' => 3600, // 1 час
    'batch_size' => 10, // Сообщений за итерацию
    'empty_wait' => 5, // Секунд при пустой очереди
    'error_wait' => 10, // Секунд при ошибке
];

while(time() - $startTime < $config['max_runtime']) {
    try{
        $envelopes = $transport->get($config['batch_size']);
        if (empty($envelopes)) {
            sleep($config['empty_wait']);
            continue;
        }

        foreach($envelopes as $envelope){


            if (memory_get_usage() > $config['memory_limit']) {
                $logger->info("Memory limit reached, restarting");
                break 2;
            }

            $message = $envelope->getMessage();
            $messageClass = get_class($message);

            if (!isset($handlers[$messageClass])) {
                $logger->error('No handler found', ['message_class' => $messageClass]);
                continue;
            }

            try{
                $handlers[$messageClass]->handle($message);
                $transport->ack($envelope);
                $processedCount++;
            }catch(\Throwable $e){
                $logger->error('Message processing failed', [
                    'exception' => $e->getMessage(),
                ]);
            }

        }
    }catch (\Throwable $e){
        $logger->critical('Worker error', [
            'exception' => $e,
            'processed_count' => $processedCount
        ]);
        sleep($config['error_wait']);
    }
}



