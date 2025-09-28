<?php

declare(strict_types=1);

use Monolog\Handler\TelegramBotHandler;
use Monolog\Level;

return [
  TelegramBotHandler::class => function () {
    return new TelegramBotHandler(
        $_ENV['TELEGRAM_TOKEN'],
        $_ENV['TELEGRAM_CHANNEL'],
        Level::Error,
        true,
        'HTML'
    );
  }
];
