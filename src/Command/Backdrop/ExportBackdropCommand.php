<?php

namespace App\Command\Backdrop;

use App\Entity\BackdropAlbum;
use App\Repository\BackdropAlbumRepository;
use App\Service\BackdropManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('backdrop:export')]
class ExportBackdropCommand extends Command
{
    public function __construct(
        private readonly BackdropAlbumRepository $albumRepository,
        private readonly BackdropManager $backdropManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('album', InputArgument::REQUIRED, 'The album id');
        $this->addArgument('outpath', InputArgument::REQUIRED, 'The file to output (.phbd)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $id = $input->getArgument('album');
        $outPath = $input->getArgument('outpath');

        /** @var BackdropAlbum|null $album */
        $album = $this->albumRepository->find($id);

        if (!$album) {
            $style->error(\sprintf('Backdrop album %s not found.', $id));

            return Command::FAILURE;
        }

        $style->info(\sprintf('Exporting backdrop album %s by %s', $album->getTitle(), $album->getAuthor()));

        try {
            $this->backdropManager->export($album, $outPath);
            $style->success('Backdrop exported successfully to '.$outPath);
        } catch (\Exception $e) {
            $style->error(\sprintf('Backdrop %s export error: %s', $album->getId(), $e->getMessage()));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
