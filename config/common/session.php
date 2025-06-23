<?php

declare(strict_types=1);

use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;

return [
    SessionInterface::class => function () {
        $session = new PhpSession();
        $session->setOptions([
            'name' => 'hr-docs',
            'lifetime' => 7200,
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        if(session_status() !== PHP_SESSION_ACTIVE){
            $session->start();
        }

        return $session;
    }
];
