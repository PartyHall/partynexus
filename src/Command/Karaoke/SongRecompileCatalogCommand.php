<?php

namespace App\Command\Karaoke;

use App\Entity\Song;
use App\Repository\SongRepository;
use App\Service\SongCompiler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('karaoke:song:recompile-catalog')]
class SongRecompileCatalogCommand extends Command
{
    public function __construct(
        private readonly SongRepository $repository,
        private readonly SongCompiler $compilator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $style->info('This command should only be used if requested by the changelog when upgrading version of PartyNexus / PartyHall.');
        $style->info('This will recompile ALL songs, thus any appliance that have the songs synced will delete them all and redownload them !');

        $iknowwhatimdoing = $style->confirm('Are you sure you want to run this command?', false);

        if (!$iknowwhatimdoing) {
            $style->error('Cancelling...');

            return Command::FAILURE;
        }

        $songs = $this->repository->findAll();

        /** @var Song $song */
        foreach ($songs as $song) {
            $style->comment(\sprintf('Decompiling %s by %s', $song->getTitle(), $song->getArtist()));
            $this->compilator->decompile($song);

            $style->comment(\sprintf('Compiling %s by %s', $song->getTitle(), $song->getArtist()));
            $this->compilator->compile($song);
        }

        $style->success('All songs have been re-compiled!');

        return Command::SUCCESS;
    }
}
