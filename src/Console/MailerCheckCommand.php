<?php

namespace App\Console;


use App\Http\Services\Mail\PhpMailSender;
use PHPMailer\PHPMailer\DSNConfigurator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

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
        $dsnString = 'smtp://mailer:1025';
        $mailer = DSNConfigurator::mailer($dsnString);
        $mailer->setFrom('admin@kd-docs.ru');
        $mailer->Body = 'Some text';
        $mailer->addAddress('user@app.test');
        try {
            // Отправка письма
            if ($mailer->send()) {
                $output->writeln("<info>Email sent successfully to MailHog!</info>");
                $output->writeln("<info>Check MailHog UI: http://localhost:8082</info>");
            } else {
                $output->writeln("<error>Failed to send email: " . $mailer->ErrorInfo . "</error>");
            }
        } catch (\Exception $e) {
            $output->writeln("<error>Error: " . $e->getMessage() . "</error>");
        }
        $output->writeln("<info>Done..</info>");

        return Command::SUCCESS;
    }
}
