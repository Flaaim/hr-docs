<?php

declare(strict_types=1);

use App\Http\Services\Mail\Mail;
use App\Http\Services\Mail\PhpMailSender;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mailer\EventListener\EnvelopeListener;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Mime\Address;
use Twig\Environment;

return [
    Mailer::class => function (ContainerInterface $container) {
        $config = $container->get('mailer')['dsn'];
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
    PhpMailSender::class => function (ContainerInterface $container) {
        return new PhpMailSender(
            $container->get(Mailer::class),
            $container->get(LoggerInterface::class)
        );
    },
    Mail::class => function (ContainerInterface $container) {
        return new Mail(
            $container->get(PhpMailSender::class),
            $container->get(Environment::class)
        );
    },
    'mailer' => [
        'dsn' => [
            'host' => $_ENV['MAIL_HOST'],
            'port' => $_ENV['MAIL_PORT'],
            'encryption' => $_ENV['MAIL_ENCRYPTION'],
            'username' => $_ENV['MAIL_USERNAME'],
            'password' => $_ENV['MAIL_PASSWORD'],
            'from' => ['email' => $_ENV['MAIL_FROM_ADDRESS'], 'name' => $_ENV['MAIL_FROM_NAME']],
        ]
    ]
];
