<?php

declare(strict_types=1);

use App\Http\Queue\Handlers\Email\EmailResetHandler;
use App\Http\Queue\Handlers\Email\EmailVerificationHandler;
use App\Http\Queue\Messages\Email\EmailResetMessage;
use App\Http\Queue\Messages\Email\EmailVerificationMessage;
use App\Http\Services\Mail\Mail;
use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection as DoctrineConnection;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;


return [
    'doctrine.messenger.transport' => function (ContainerInterface $c) {
        return new DoctrineTransport(
            new DoctrineConnection([], $c->get(Connection::class)),
           new PhpSerializer()
        );
    },

    'doctrine.messenger.handlers' => function (ContainerInterface $c) {
        return [
            EmailVerificationMessage::class  => new EmailVerificationHandler($c->get(Mail::class), $c->get(LoggerInterface::class)),
            EmailResetMessage::class  => new EmailResetHandler($c->get(Mail::class), $c->get(LoggerInterface::class))
        ];
    },

    MessageBus::class => function (ContainerInterface $c) {
        return new MessageBus([
            $c->get(SendMessageMiddleware::class),
            $c->get(HandleMessageMiddleware::class)
        ]);
    },

    SendMessageMiddleware::class => function (ContainerInterface $c) {
        return new SendMessageMiddleware(
            new SendersLocator(
                [
                    EmailVerificationMessage::class => ['doctrine.messenger.transport'],
                    EmailResetMessage::class => ['doctrine.messenger.transport'],
                ],
                $c
            )
        );
    },

    HandleMessageMiddleware::class => function (ContainerInterface $c) {
        return new HandleMessageMiddleware(
            new HandlersLocator([
                $c->get('doctrine.messenger.handlers')
            ])
        );
    },
];
