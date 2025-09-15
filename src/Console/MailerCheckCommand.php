<?php

namespace App\Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;

class MailerCheckCommand extends Command
{
    public function configure(): void
    {
        $this->setName('mailer:check');
        $this->setDescription('Check mailer');

    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("<info>Checking mailer...</info>");
        $transport = new EsmtpTransport(
            'mailer',
                1025
        );
        $mailer = new Mailer($transport);
        $message = (new Email())
            ->subject('Test email')
            ->from('mail@app.test')
            ->to('user@app.test')
            ->text('Test email');


        $mailer->send($message);

        $output->writeln("<info>Done..</info>");

        return Command::SUCCESS;
    }
}
