<?php

namespace App\Service;

use App\Entity\Song;
use App\Enum\SongFormat;
use App\Utils\MediaUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

readonly class SongCompiler
{
    public function __construct(
        private Filesystem $fs,
        private EntityManagerInterface $emi,
        private SerializerInterface $serializer,
        #[Autowire(env: 'SONG_EXTRACT_LOCATION')]
        private string $wipLocation,
        #[Autowire(env: 'SONG_LOCATION')]
        private string $compiledLocation,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function decompile(Song $song): void
    {
        $compiledFile = Path::join($this->compiledLocation, $song->getId().'.phk');
        if (!$this->fs->exists($compiledFile)) {
            throw new \Exception('The compiled file is missing');
        }

        $wipDir = Path::join($this->wipLocation, \sprintf('%s', $song->getId()));
        $this->fs->remove($wipDir);
        $this->fs->mkdir($wipDir);

        $zip = new \ZipArchive();
        if (true === $zip->open($compiledFile)) {
            $zip->extractTo($wipDir);
            $zip->close();
        } else {
            throw new \Exception('Failed to extract the compiled file');
        }

        $song->setReady(false);
        $song->setNexusBuildId(null);
        $this->fs->remove($compiledFile);

        $this->emi->persist($song);
        $this->emi->flush();
    }

    /**
     * @throws \Exception
     */
    public function compile(Song $song): void
    {
        $wipDir = Path::join($this->wipLocation, \sprintf('%s', $song->getId()));
        if (!$this->fs->exists($wipDir)) {
            throw new \Exception('The decompiled files are missing');
        }

        $compiledFile = Path::join($this->compiledLocation, $song->getId().'.phk');
        $this->fs->mkdir($this->compiledLocation);
        $this->fs->remove($compiledFile);

        $song->setNexusBuildId(Uuid::v4());
        $song->setReady(true);

        // Resize cover to 300x300

        // We update the cover on the entity
        if (\file_exists(Path::join($wipDir, 'cover.jpg'))) {
            $coverFile = Path::join(sys_get_temp_dir(), 'cover.jpg');

            $this->fs->copy(Path::join($wipDir, 'cover.jpg'), $coverFile);
            $song->setCoverFile(new UploadedFile(
                $coverFile,
                'cover.jpg',
                'image/jpeg',
                test: true,
            ));
        }

        if (SongFormat::CDG === $song->getFormat()) {
            $instrumental = Path::join($wipDir, 'instrumental.mp3');
            $cdg = Path::join($wipDir, 'lyrics.cdg');

            if (!\file_exists($instrumental) || !\file_exists($cdg)) {
                throw new \Exception('The instrumental file / lyrics file is missing');
            }

            $duration = MediaUtils::getMediaDuration($instrumental);
            if (false === $duration) {
                throw new \Exception('Failed to get the media duration!');
            }

            $song->setDuration($duration);
        } else {
            $instrumental = Path::join($wipDir, 'instrumental.webm');

            if (!\file_exists($instrumental)) {
                throw new \Exception('The instrumental file / lyrics file is missing');
            }

            $duration = MediaUtils::getMediaDuration($instrumental);
            if (false === $duration) {
                throw new \Exception('Failed to get the media duration!');
            }

            $song->setDuration($duration);
        }

        $vocals = Path::join($wipDir, 'vocals.mp3');
        $song->setVocals(\file_exists($vocals));

        $full = Path::join($wipDir, 'full.mp3');
        $song->setCombined(\file_exists($full));

        $this->emi->persist($song);
        $this->emi->flush();

        // Building the main json metadata file
        $meta = $this->serializer->serialize(
            $song,
            'json',
            [
                AbstractNormalizer::GROUPS => [Song::COMPILE_METADATA],
                'json_encode_options' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
            ]
        );
        $metaFile = Path::join($wipDir, 'song.json');
        $this->fs->remove($metaFile);
        $this->fs->appendToFile($metaFile, $meta);

        $zip = new \ZipArchive();
        if (!$zip->open($compiledFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            throw new \Exception('Failed to create the PHK archive');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($wipDir),
            \RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($wipDir) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        $this->fs->remove($wipDir);
    }
}
