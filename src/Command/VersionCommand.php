<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand('version')]
class VersionCommand extends Command
{
    public function __construct(
        #[Autowire(param: 'PARTYNEXUS_VERSION')]
        private readonly string $partynexusVersion,
        #[Autowire(param: 'PARTYNEXUS_COMMIT')]
        private readonly string $partynexusCommit,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            \sprintf(
                'PartyNexus version %s (Commit %s)',
                $this->partynexusVersion,
                $this->partynexusCommit,
            ),
        );

        return Command::SUCCESS;
    }
}
