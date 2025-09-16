<?php

namespace App\Command\Karaoke;

use App\Repository\SongRepository;
use App\Service\SongCompiler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * I get 502 timeouts on compileing a song.
 *
 * this should maybe be done in a worker idk, idc
 * lets make a simple command, too lazy to change php config
 * to allow longer execution time
 */
#[AsCommand('karaoke:song:compile')]
class KaraokeCompileSongCommand extends Command
{
    public function __construct(
        private SongRepository $songRepository,
        private SongCompiler $compiler,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('song', InputArgument::REQUIRED, 'The song to compile');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $song = $this->songRepository->find($input->getArgument('song'));

        if (!$song) {
            $output->writeln('<error>Song not found!</error>');

            return Command::FAILURE;
        }

        if ($song->isReady()) {
            $output->writeln('<info>Song is already compiled and ready!</info>');

            return Command::SUCCESS;
        }

        $output->writeln('<info>Compiling song...</info>');
        try {
            $this->compiler->compile($song);
            $output->writeln('<info>Song compiled successfully!</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Error compiling song: '.$e->getMessage().'</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
