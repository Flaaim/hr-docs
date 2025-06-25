<?php

declare(strict_types=1);

use App\Http\Services\Mail\Mail;
use App\Http\Services\Mail\PhpMailSender;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Twig\Environment;

return [
    PhpMailSender::class => function () {
        return new PhpMailSender(
            new PHPMailer(true)
        );
    },
    Mail::class => function (ContainerInterface $container) {
        return new Mail($container->get(PhpMailSender::class), $container->get(Environment::class));
    }

];
