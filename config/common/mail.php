<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mailer\EventListener\EnvelopeListener;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;

return [
    MailerInterface::class => function (ContainerInterface $container) {
        $config = $container->get('config')['mailer'];
        $dispatcher = new EventDispatcher();

        $dispatcher->addSubscriber(
            new EnvelopeListener(
                new Address(
                    $config['from']['email'],
                    $config['from']['name']
                ),
            ),
        );
        $transport = (new EsmtpTransport(
            $config['host'],
            (int)$config['port'],
            $config['encryption'] === 'tls',
            $dispatcher
        ))
            ->setUsername($config['username'])
            ->setPassword($config['password']);


        return new Mailer(
            $transport
        );
    },
    'config' => [
        'mailer' => [
            'host' => $_ENV['MAIL_HOST'],
            'port' => $_ENV['MAIL_PORT'],
            'encryption' => $_ENV['MAIL_ENCRYPTION'],
            'username' => $_ENV['MAIL_USERNAME'],
            'password' => $_ENV['MAIL_PASSWORD'],
            'from' => ['email' => $_ENV['MAIL_FROM_ADDRESS'], 'name' => $_ENV['MAIL_FROM_NAME']],
        ]
    ]
];
