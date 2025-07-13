<?php

declare(strict_types=1);

use App\Http\Services\Mail\EmailVerification\EmailVerificationHandler;
use App\Http\Services\Mail\Mail;
use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;


return [
    PhpSerializer::class => function () {
      return new PhpSerializer();
    },
    Serializer::class => function (ContainerInterface $container) {
        return new Serializer(
            $container->get(PhpSerializer::class),
            $container->get(PhpSerializer::class)
        );
    },
    SerializerInterface::class => function (ContainerInterface $container) {
        return $container->get(Serializer::class);
    },
    DoctrineTransport::class => function (ContainerInterface $c) {
        return new DoctrineTransport(
            $c->get(Connection::class),
            $c->get(SerializerInterface::class)
        );
    },
    MessageBus::class => function () {
        return new MessageBus();
    },

    EmailVerificationHandler::class => function (ContainerInterface $c) {
        return new EmailVerificationHandler(
            $c->get(Mail::class),
            $c->get(LoggerInterface::class),
        );
    }
];
