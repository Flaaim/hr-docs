<?php

namespace Tests\Unit\Mail;

use App\Http\Exception\Mail\MailNotSendException;
use App\Http\Services\Mail\Mail;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Http\Services\Mail\Mail
 */
class SendMailTest extends TestCase
{
    private Mail $mail;
    private array $mocks;
    public function setUp(): void
    {
        $this->mocks = MockFactory::create($this);
        $this->mail = new Mail(
            $this->mocks['sender'],
            $this->mocks['twig']
        );
    }

    public function testSendThrowsExceptionIfEmailIsEmpty()
    {
        $this->mail->setTo('')->setSubject('some subject')->setBody('some body');
        $this->expectException(MailNotSendException::class);
        $this->expectExceptionMessage('Не указан получатель письма');
        $this->mail->send();
    }
    public function testSendThrowsExceptionIfSubjectIsEmpty()
    {
        $this->mail->setTo('email@email.ru')->setSubject('')->setBody('some body');
        $this->expectException(MailNotSendException::class);
        $this->expectExceptionMessage('Не указана тема письма');
        $this->mail->send();
    }

    public function testSendThrowsExceptionIfBodyIsEmpty()
    {
        $this->mail->setTo('email@email.ru')->setSubject('some subject')->setBody('');
        $this->expectException(MailNotSendException::class);
        $this->expectExceptionMessage('Не указано содержимое письма');
        $this->mail->send();
    }
    public function testSendThrowsExceptionIfDataIsEmpty()
    {
        $this->expectException(MailNotSendException::class);
        $this->expectExceptionMessage('Data для отправки email пуста');
        $this->mail->setTo('email@email.ru')->setSubject('some subject')->setBodyFromTemplate('some template name', []);
    }

    public function testSendMailSuccessWithDirectBody()
    {
        $this->mail->setTo('email@email.ru')
            ->setSubject('some subject')
            ->setBody('some body');
        $this->mocks['sender']->expects($this->once())
            ->method('send')
            ->with('email@email.ru', 'some subject', 'some body')
            ->willReturn(true);
        $this->assertTrue($this->mail->send());


    }

    public function testSendMailSuccessWithTemplateBody()
    {
        $this->mocks['twig']->method('render')
            ->willReturn('some body');
        $this->mocks['sender']->expects($this->once())
            ->method('send')
            ->with('email@email.ru', 'some subject', 'some body')->willReturn(true);

        $this->mail->setTo('email@email.ru')
            ->setSubject('some subject')
            ->setBodyFromTemplate('some template name', ['var' => 'var']);

        $this->assertTrue($this->mail->send());
    }

    public function testSetBodyFromTemplateRendersCorrectly()
    {
        $template = 'template.html.twig';
        $data = ['name' => 'Flaaim'];
        $rendered = 'Hello Flaaim';

        $this->mocks['twig']->expects($this->once())
            ->method('render')->with($template, $data)->willReturn($rendered);
        $this->mail
            ->setTo('email@email.ru')
            ->setSubject('some subject')
            ->setBodyFromTemplate($template, $data);
        $this->mail->send();
    }
}
