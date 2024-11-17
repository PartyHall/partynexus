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
    // @TODO
    // For some reason, when using param with the ones injected
    // this does not work and use the same every time
    // Meanwhile we'll just use the env var injected by Dockerfile
    // that's not that bad since the only supported flow is with docker
    // but it should be corrected nontheless

    public function __construct(
        #[Autowire(env: 'PARTYNEXUS_VERSION')]
        private readonly string $partynexusVersion,
        #[Autowire(env: 'PARTYNEXUS_COMMIT')]
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
