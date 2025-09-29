<?php

declare(strict_types=1);

use App\Http\Log\LogFile;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;


return [
    LoggerInterface::class => function (ContainerInterface $container) {
        $config = $container->get('config')['logger'];

        $level = $config['debug'] ? Level::Debug : Level::Info;
        $logger = new Logger($config['name']);

        if($config['stderr']){
            $logger->pushHandler(new StreamHandler('php://stderr', $level));
        }

        if(!empty($config['file'])){
            $dir = dirname($config['file']);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $logger->pushHandler(new StreamHandler($config['file'], $level));
        }

        if(!empty($config['telegram_bot'])){
            $logger->pushHandler($container->get(TelegramBotHandler::class));
        }

        return $logger;
    },
    'config' => [
        'logger' => [
            'debug' => $_ENV['APP_DEBUG'],
            'file' => __DIR__ . '/../../var/log/' . PHP_SAPI . '/application.log',
            'stderr' => true,
            'telegram_bot' => true,
            'name' => $_ENV['APP_HOST'],
        ]
    ],
    LogFile::class => function (ContainerInterface $container) {
        return new LogFile($container->get('config')['logger']['file']);
    }
];
