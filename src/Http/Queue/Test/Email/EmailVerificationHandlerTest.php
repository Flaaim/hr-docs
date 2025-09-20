<?php

namespace App\Http\Queue\Test\Email;

use App\Http\Queue\Handlers\Email\EmailVerificationHandler;
use App\Http\Queue\Messages\Email\EmailVerificationMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailVerificationHandlerTest extends TestCase
{
    public function testSuccess(): void
    {
        $message = new EmailVerificationMessage(
            $to = 'user@email.ru',
            $subject = 'Подтверждение регистрации',
            $token = '123456'
        );
        $url = 'http://localhost/auth/verify?token=' . $token;
        $handler = new EmailVerificationHandler(
            $mailer = $this->createMock(MailerInterface::class),
            $this->createMock(LoggerInterface::class),
            $twig = $this->createMock(Environment::class),
        );

        $twig->expects($this->once())->method('render')->with(
            $this->equalTo('emails/welcome.html.twig'),
            $this->equalTo([
                'email' => $to,
                'verifyToken' => $token,
                'subject' => $subject
            ])
        )->willReturn($this->expectedHtml());

        $mailer->expects($this->once())->method('send')->willReturnCallback(static function (Email $email) use ($to, $url, $subject) {
            self::assertEquals([new Address($to)], $email->getTo());
            self::assertEquals($subject, $email->getSubject());
            self::assertStringContainsString($url, $email->getHtmlBody());
        });

        $handler->handle($message);
    }

    public function testFailed(): void
    {
        $message = new EmailVerificationMessage(
            $to = 'user@email.ru',
            $subject = 'Подтверждение регистрации',
            $token = '123456'
        );

        $url = 'http://localhost/auth/verify?token=' . $token;
        $handler = new EmailVerificationHandler(
            $mailer = $this->createMock(MailerInterface::class),
            $this->createMock(LoggerInterface::class),
            $twig = $this->createMock(Environment::class),
        );

        $mailer->expects($this->once())->method('send')->willThrowException(new TransportException());
        $handler->handle($message);
    }
    private function expectedHtml(): string
    {
        return '<h1>Добро пожаловать!</h1>
        <p>
            Вы зарегистрировались на сайте.
        </p>
        <p>
            Для продолжения, используйте, пожалуйста, данную ссылку: http://localhost/auth/verify?token=123456
        </p>';
    }
}
