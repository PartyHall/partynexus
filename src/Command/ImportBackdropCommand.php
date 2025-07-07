<?php

namespace App\Command;

use App\Service\BackdropManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('backdrop:import')]
class ImportBackdropCommand extends Command
{
    public function __construct(
        private readonly BackdropManager $backdropManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'The file to import (.phbd)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $file = $input->getArgument('file');

        try {
            $album = $this->backdropManager->import($file);

            if (!$album) {
                $style->error('An issue occured while importing the album. No exception was thrown, but the album is null. Please report this issue on PartyNexus with an album file.');

                return Command::FAILURE;
            }

            $style->success(\sprintf(
                'Backdrop album %s by %s imported successfully (id %s)',
                $album->getTitle(),
                $album->getAuthor(),
                $album->getId(),
            ));
        } catch (\Exception $e) {
            $style->error(\sprintf('Backdrop import error: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
