<?php

namespace App\Service;

use App\Entity\Backdrop;
use App\Entity\BackdropAlbum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

readonly class BackdropManager
{
    public function __construct(
        private SerializerInterface $serializer,
        private Filesystem $filesystem,
        private StorageInterface $storage,
        private EntityManagerInterface $emi,
    ) {
    }

    public function import(string $zipFilePath): ?BackdropAlbum
    {
        $zip = new \ZipArchive();
        if (true !== $zip->open($zipFilePath)) {
            throw new \RuntimeException('Invalid .phbd file format');
        }

        $extractDir = \sys_get_temp_dir().'/backdrop_import_'.\uniqid();
        $this->filesystem->mkdir($extractDir);

        try {
            $zip->extractTo($extractDir);
            $zip->close();

            $metadata = \file_get_contents($extractDir.'/metadata.json');
            if (false === $metadata) {
                throw new \RuntimeException('Corrupted .phbd file: metadata.json not found or unreadable');
            }

            $backdrops = \json_decode($metadata, true)['backdrops'];

            /** @var BackdropAlbum $album */
            $album = $this->serializer->deserialize($metadata, BackdropAlbum::class, 'json', [
                AbstractNormalizer::GROUPS => [BackdropAlbum::IMPORT],
            ]);

            foreach ($backdrops as $backdrop) {
                $filename = $backdrop['filepath'];
                $filePath = $extractDir.'/'.$filename;

                if (!\file_exists($filePath)) {
                    throw new \RuntimeException(sprintf('Corrupted .phbd file: file %s referenced in metadata not found', $filename));
                }

                $uploadedFile = new UploadedFile(
                    $filePath,
                    $filename,
                    null,
                    null,
                    true
                );

                (new Backdrop())
                    ->setTitle($backdrop['title'])
                    ->setAlbum($album)
                    ->setFile($uploadedFile)
                ;
            }

            $this->emi->persist($album);
            $this->emi->flush();

            return $album;
        } finally {
            $this->filesystem->remove($extractDir);
        }
    }

    public function export(BackdropAlbum $album, string $outPath): void
    {
        $zip = new \ZipArchive();

        if (true !== $zip->open($outPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            throw new \RuntimeException('Failed to create zip file');
        }

        try {
            $metadata = $this->serializer->serialize($album, 'json', [
                AbstractNormalizer::GROUPS => [BackdropAlbum::EXPORT],
                'json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ]);

            $zip->addFromString('metadata.json', $metadata);

            foreach ($album->getBackdrops() as $backdrop) {
                $filePath = $this->storage->resolvePath($backdrop, 'file');

                if (!$filePath || !file_exists($filePath)) {
                    throw new \RuntimeException(sprintf('File not found: %s', $filePath));
                }

                $zip->addFile($filePath, basename($filePath));
            }
        } finally {
            $zip->close();
        }
    }
}
