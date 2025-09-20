<?php

namespace App\Http\Queue\Test\Email;

use App\Http\Queue\Handlers\Email\EmailPaymentHandler;
use App\Http\Queue\Messages\Email\EmailPaymentMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailPaymentHandlerTest extends TestCase
{
    public function testSuccess(): void{
        $emailPaymentMessage = new EmailPaymentMessage(
            'user@test.ru',
            'Оплата подписки',
            '300',
            'monthly'
        );


        $emailPaymentHandler = new EmailPaymentHandler(
            $mailer = $this->createMock(MailerInterface::class),
            $this->createMock(LoggerInterface::class),
            $twig = $this->createMock(Environment::class)
        );

        $twig->expects($this->once())->method('render')
            ->with(
                $this->equalTo('emails/payment.html.twig'),
                $this->equalTo([
                    'email' => $emailPaymentMessage->email,
                    'subject' => $emailPaymentMessage->subject,
                    'amount' => $emailPaymentMessage->amount,
                    'slug' => $emailPaymentMessage->slug
                ])
            )->willReturn($this->expectedHtml());

        $mailer->expects($this->once())->method('send')
            ->willReturnCallback(static function (Email $email) use ($emailPaymentMessage) {
                self::assertEquals([Address::create($emailPaymentMessage->email)], $email->getTo());
                self::assertEquals($emailPaymentMessage->subject, $email->getSubject());
                self::assertStringContainsString('300', $email->getHtmlBody());
        });

        $emailPaymentHandler->handle($emailPaymentMessage);
    }

    private function expectedHtml(): string
    {
        return '<h1>Оплата подписки</h1>
        <p>
            Платеж успешно проведен. Ваш план подписки обновлен.
        </p>
        <p>Сумма: 300</p>
        <p>

            План подписки:
            Ежемесячный
            Действует до
            Неограниченный доступ к документам сайта.
        </p>';
    }
}
