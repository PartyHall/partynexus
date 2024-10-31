<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Export;
use App\Entity\Picture;
use App\Enum\ExportProgress;
use App\Enum\ExportStatus;
use App\Utils\DirectoryUtils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

readonly class EventExporter
{
    private Export $export;
    private Event $event;
    private string $tempPath;
    private string $outPath;

    public function __construct(
        private LoggerInterface     $logger,
        private Filesystem          $fs,
        private StorageInterface    $storage,
        private MimeTypesInterface  $mimeTypes,
        private SerializerInterface $serializer,
        private EntityManagerInterface $emi,
        #[Autowire(env: 'EXPORTS_LOCATION')]
        private string $exportLocation,
    )
    {
    }

    private function setStatus(ExportProgress $progress): void
    {
        $this->export->setProgress($progress);
        $this->emi->persist($this->export);
        $this->emi->flush();
    }

    /**
     * @throws \Exception
     */
    public function exportEvent(Event $event): void
    {
        $this->event = $event;
        $this->export = ($event->getExport() ?? (new Export()))
            ->setEvent($event)
            ->setStartedAt(new \DateTimeImmutable())
            ->setEndedAt(null)
            ->setStatus(ExportStatus::STARTED)
            ->setProgress(ExportProgress::STARTED)
        ;

        $this->event->setExport($this->export);
        $this->emi->persist($this->export);
        $this->emi->flush();

        $this->outPath = Path::join($this->exportLocation, $this->event->getId()->toString() . '.zip');
        $this->fs->mkdir($this->exportLocation);

        $this->logger->info('Exporting event', ['event' => $event->getId()->toString()]);

        try {
            $this->tempPath = DirectoryUtils::tempdir();
            $this->fs->mkdir($this->tempPath);

            $this->logger->info('Adding photobooth pictures', ['event' => $event->getId()->toString()]);
            $this->addPictures();

            $this->logger->info('Generating timelapse', ['event' => $event->getId()->toString()]);
            $this->addTimelapse();

            $this->logger->info('Adding metadata file', ['event' => $event->getId()->toString()]);
            $this->addMetadata();

            $this->logger->info('Generating final zip file', ['event' => $event->getId()->toString()]);
            $this->buildZip();

            $this->logger->info('Saving export', ['event' => $event->getId()->toString()]);
            $this->saveExport();
        } catch (\Exception $e) {
            $this->logger->error('Failed to export event', ['exception' => $e]);

            $this->export->setStatus(ExportStatus::FAILED);
            $this->emi->persist($this->export);
            $this->emi->flush();
        }
    }

    private function addPictures(): void
    {
        $this->setStatus(ExportProgress::ADDING_PICTURES);

        $exportPicturePath = Path::join($this->tempPath, 'pictures');
        $this->fs->mkdir($exportPicturePath);

        /** @var Picture $picture */
        foreach ($this->event->getPictures() as $picture) {
            if ($picture->isUnattended()) {
                continue;
            }

            $ext = $this->mimeTypes->getExtensions($picture->getFileMimetype());
            $ext = $ext[0] ?? null;

            if (!$ext) {
                $this->logger->error('Failed to determine file extension for mimetype', [
                    'event' => $this->event->getId()->toString(),
                    'picture' => $picture->getId()->toString(),
                    'mimetype' => $picture->getFileMimetype(),
                ]);
                continue;
            }

            $sourceFilePath = $this->storage->resolvePath($picture, 'file');
            $outFile = Path::join(
                $exportPicturePath,
                \sprintf(
                    '%s.%s',
                    $picture->getTakenAt()->format('Y-m-d_H-i-s'),
                    $ext,
                ),
            );

            $this->fs->copy($sourceFilePath, $outFile);

            $this->logger->info("Picture added to export", ['file' => $outFile, 'event' => $this->event->getId()->toString()]);
        }
    }

    private function addTimelapse(): void
    {
        $this->setStatus(ExportProgress::GENERATING_TIMELAPSE);
        $unattendedPictures = $this
            ->event
            ->getPictures()
            ->filter(fn(Picture $picture) => $picture->isUnattended())
            ->toArray();
        $amtPictures = \count($unattendedPictures);
        if ($amtPictures === 0) {
            $this->logger->warning('No unattended picture found, skipping timelapse generation', ['event' => $this->event->getId()->toString()]);
            return;
        }

        \usort($unattendedPictures, fn(Picture $a, Picture $b) => $a->getTakenAt() <=> $b->getTakenAt());

        $files = \array_map(
            fn(Picture $p) => \sprintf("file '%s'", $this->storage->resolvePath($p, 'file')),
            $unattendedPictures,
        );

        // Building the listing file
        $listFile = Path::join($this->tempPath, 'timelapse.txt');
        $this->fs->appendToFile($listFile, join("\n", $files));

        // Running ffmpeg to build this timelapse
        $outFile = Path::join($this->tempPath, 'timelapse.mp4');
        $this->fs->remove($outFile);

        $framerate = ($amtPictures / 10); // We target 10s video

        // We clamp the framerate between 6fps and 15fps to have
        // a nice timelapse
        $framerate = (int)floor(max(min(15, $framerate), 6));

        // @TODO: Do properly ?
        exec(\sprintf(
            'ffmpeg -f concat -safe 0 -i %s -vf "settb=AVTB,setpts=N/%s/TB,fps=%s" -c:v libx264 %s',
            $listFile,
            $framerate,
            $framerate,
            $outFile,
        ));

        $this->fs->remove($listFile);
        $this->logger->info('Timelapse added', ['event' => $this->event->getId()->toString()]);
    }

    private function addMetadata(): void
    {
        $this->setStatus(ExportProgress::ADDING_METADATA);
        $metadata = $this->serializer->serialize(
            $this->event,
            'json',
            [
                'groups' => [Event::API_EXPORT],
                'json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION,
            ],
        );

        $exportMetadataPath = Path::join($this->tempPath, 'metadata.json');

        $this->fs->appendToFile($exportMetadataPath, $metadata);
        $this->logger->info("Metadata added to the export", ['event' => $this->event->getId()->toString()]);
    }

    private function buildZip(): void
    {
        $this->setStatus(ExportProgress::BUILDING_ZIP);

        $this->fs->remove($this->outPath);
        $zip = new \ZipArchive();

        if (!$zip->open($this->outPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            $this->logger->error("Failed to create ZIP file at '{$this->outPath}'.", ['event' => $this->event->getId()->toString()]);
            throw new \Exception("Failed to create ZIP file at '{$this->outPath}'");
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->tempPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($this->tempPath) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }

    private function saveExport(): void
    {
        $this->export->setEndedAt(new \DateTimeImmutable());
        $this->export->setStatus(ExportStatus::COMPLETE);
        $this->emi->persist($this->export);
        $this->emi->flush();
    }
}
