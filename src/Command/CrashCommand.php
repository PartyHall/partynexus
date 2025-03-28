<?php

namespace App\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('test:sentry')]
class CrashCommand extends Command
{
    public function __construct(private LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setHelp('Simple command that triggers an exception and logs an error. Used to test that sentry is working');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // the following code will test if monolog integration logs to sentry
        $this->logger->error('My custom command logged error.', ['some' => 'Context Data']);
        // the following code will test if an uncaught exception logs to sentry
        throw new \RuntimeException('Command example exception');

        return 0;
    }
}
