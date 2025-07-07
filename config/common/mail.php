<?php

declare(strict_types=1);

use App\Http\Services\Mail\Mail;
use App\Http\Services\Mail\PhpMailSender;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

return [
    PhpMailSender::class => function (ContainerInterface $container) {
        return new PhpMailSender(
            new PHPMailer(true),
            $container->get(LoggerInterface::class)
        );
    },
    Mail::class => function (ContainerInterface $container) {
        return new Mail($container->get(PhpMailSender::class), $container->get(Environment::class));
    }

];
