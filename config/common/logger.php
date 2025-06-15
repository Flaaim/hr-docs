<?php

declare(strict_types=1);

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
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
        $handler = new StreamHandler(
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'var'. DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'error.log',
            Level::Warning // Логирует WARNING, ERROR, CRITICAL, ALERT, EMERGENCY
        );
        $handler->setFormatter($formatter);
        // Обработчик для ошибок (уровень WARNING и выше)
        $logger->pushHandler($handler);

        return $logger;
    },
];
