<?php

namespace App\Command;

use App\Entity\Song;
use App\Enum\SongFormat;
use App\Enum\SongQuality;
use App\Service\SongCompilator;
use App\Utils\DirectoryUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

#[AsCommand('karaoke:song:import-legacy')]
class KaraokeImportLegacyCommand extends Command
{
    public function __construct(
        private readonly Filesystem $fs,
        private readonly SongCompilator $compilator,
        private readonly EntityManagerInterface $emi,
        #[Autowire(env: 'SONG_EXTRACT_LOCATION')]
        private readonly string $wipLocation,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'path',
            InputArgument::REQUIRED,
            'The file of your legacy song or the folder in which your legacy songs reside',
        );
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $basePath = $input->getArgument('path');
        $style = new SymfonyStyle($input, $output);

        if (!$this->fs->exists($basePath)) {
            $style->error('The path does not exist!');

            return Command::FAILURE;
        }

        if (\is_dir($basePath)) {
            $files = array_diff(scandir($basePath), ['..', '.']);
            foreach ($files as $file) {
                $this->importSong($style, Path::join($basePath, $file));
            }
        } else {
            $this->importSong($style, $basePath);
        }

        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    private function importSong(SymfonyStyle $style, string $path): void
    {
        if ('phk' !== \strtolower(\pathinfo($path, PATHINFO_EXTENSION))) {
            $style->error("The file is not a phk file. Are you sure it's in the correct format?");

            return;
        }

        $style->info('Importing Song: '.$path);
        $tempDir = DirectoryUtils::tempdir(prefix: 'legsongimp');

        if (!$tempDir) {
            throw new \Exception('Failed to create temporary directory');
        }

        try {
            // Extracting the file
            $zip = new \ZipArchive();

            if (!$zip->open($path)) {
                throw new \Exception('Failed to open phk file');
            }

            if (!$zip->extractTo($tempDir)) {
                throw new \Exception('Failed to extract phk file');
            }

            $zip->close();

            // Create a non ready song from song.json file
            $meta = json_decode(file_get_contents(Path::Join($tempDir, 'song.json')), true);
            $song = (new Song())
                ->setTitle($meta['title'])
                ->setArtist($meta['artist'])
                ->setSpotifyId($meta['spotify_id']);

            if ($meta['hotspot']) {
                list($h, $m, $s) = explode(':', $meta['hotspot']);

                $song->setHotspot(
                    intval($h) * 3600
                    + intval($m) * 60
                    + intval($s)
                );
            }

            $format = \strtoupper($meta['format']);
            if ('CDG' === $format) {
                $song->setFormat(SongFormat::CDG);
            } elseif ('WEBM' === $format || 'MP4' === $format) {
                $song->setFormat(SongFormat::VIDEO);
            } else {
                throw new \Exception('Unknown format: '.$meta['format']);
            }

            // The quality wasn't a real thing at this time so defaulting to OK
            $song->setQuality(SongQuality::OK);

            // Add the files at the correct path
            $coverFile = Path::join($tempDir, 'cover.jpg');
            $song->setCover(\file_exists($coverFile));

            $this->emi->persist($song);
            $this->emi->flush();

            // We remove the old song.json, the compiler will do the job
            $this->fs->remove(Path::join($tempDir, 'song.json'));

            // Now the song is at its proper place
            $outDir = Path::join(
                $this->wipLocation,
                $song->getId(),
            );
            $this->fs->remove($outDir);
            $this->fs->rename(
                $tempDir,
                $outDir,
            );

            $this->compilator->compile($song);
        } catch (\Exception $e) {
            $style->error($e->getMessage());
            throw $e;
        } finally {
            $this->fs->remove($tempDir);
        }
    }
}
