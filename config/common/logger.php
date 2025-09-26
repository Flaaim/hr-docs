<?php

declare(strict_types=1);

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;


return [
    LoggerInterface::class => function (ContainerInterface $container) {
        $logger = new Logger('app');

        $formatter = new LineFormatter(
            "[%datetime%] %level_name%: %message% %context%\n",
            "Y-m-d H:i:s",
            true, // allowInlineLineBreaks
            true  // ignoreEmptyContextAndExtra
        );
        $fileHandler = new StreamHandler(
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'var'. DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'error.log',
            Level::Warning // Логирует WARNING, ERROR, CRITICAL, ALERT, EMERGENCY
        );
        $fileHandler->setFormatter($formatter);
        $logger->pushHandler($fileHandler);

        $telegramHandler = $container->get(TelegramBotHandler::class);
        $telegramHandler->setFormatter($formatter);
        $logger->pushHandler($telegramHandler);;

        return $logger;
    },
];
