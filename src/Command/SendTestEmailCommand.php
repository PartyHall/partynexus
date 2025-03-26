<?php

namespace App\Command;

use App\Message\TestEmailNotification;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand('emails:send:test')]
class SendTestEmailCommand extends Command
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'The email address');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->messageBus->dispatch(new TestEmailNotification($input->getArgument('email')));
        $output->writeln('Test email sent!');

        return Command::SUCCESS;
    }
}
