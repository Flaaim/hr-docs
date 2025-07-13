#!/usr/bin/env php
<?php

use App\Http\Services\Mail\EmailVerification\EmailVerificationHandler;
use App\Http\Services\Mail\EmailVerification\EmailVerificationMessage;
use App\Http\Services\Mail\Mail;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;

require __DIR__.'/../vendor/autoload.php';

// 1. Подключаем контейнер с настройками
$container = require __DIR__.'/../config/container.php';

$transport = $container->get(DoctrineTransport::class);
$handler = $container->get(EmailVerificationHandler::class);


while (true) {
    $envelopes = $transport->get();
    if(count($envelopes) > 0){
        foreach($envelopes as $envelope){
            try{
                $message = $envelope->getMessage();

                if ($message instanceof EmailVerificationMessage) {
                    $handler->handle($message);
                }
                $transport->ack($envelope);
            }catch (\Exception $exception){
                $transport->reject($envelope);
            }

        }
    }
}
