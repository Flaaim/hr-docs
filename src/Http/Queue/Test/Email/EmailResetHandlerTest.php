<?php

namespace App\Http\Queue\Test\Email;

use App\Http\Frontend\FrontendUrlGenerator;
use App\Http\Queue\Handlers\Email\EmailResetHandler;
use App\Http\Queue\Messages\Email\EmailResetMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use Symfony\Component\Mime\Email;

class EmailResetHandlerTest extends TestCase
{
    public function testSuccess(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $message =  new EmailResetMessage(
            'some@email.ru',
            $subject = 'Сброс пароля',
            $token = '123456',
        );
        $url = 'http://localhost/reset?token=123456';
        $emailResetHandler = new EmailResetHandler(
            $mailer,
            $this->createMock(LoggerInterface::class),
            $twig = $this->createMock(Environment::class),
        );


        $twig->expects($this->once())->method('render')->with(
            $this->equalTo('emails/reset.html.twig'),
            $this->equalTo(['token' => $token, 'subject' => $subject])
        )->willReturn($url);

        $mailer->expects($this->once())->method('send')
            ->willReturnCallback(static function (Email $email) use ($message, $url) {
                self::assertEquals([Address::create($message->email)], $email->getTo());

                self::assertStringContainsString($url, $email->getHtmlBody());

        });

        $emailResetHandler->handle($message);
    }
}
