<?php

namespace App\EventListener;

use App\Entity\Song;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Doctrine\ORM\Events as DoctrineEvents;

#[AsEntityListener(event: DoctrineEvents::postPersist, entity: Song::class)]
#[AsEntityListener(event: DoctrineEvents::postUpdate, entity: Song::class)]
class SongCoverUpdatedListener implements EventSubscriberInterface
{
    /**
     * @var array<mixed>
     */
    private array $pendingSongs = [];

    public function __construct(
        #[Autowire(env: 'SONG_EXTRACT_LOCATION')]
        private readonly string                 $wipLocation,
        private readonly Filesystem             $fs,
        private readonly EntityManagerInterface $emi,
    )
    {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_UPLOAD => 'onPostUpload',
        ];
    }

    public function onPostUpload(Event $event): void
    {
        $song = $event->getObject();
        if (!$song instanceof Song) {
            return;
        }

        $this->pendingSongs[\spl_object_id($song)] = [
            'entity' => $song,
            'mapping' => $event->getMapping(),
        ];
    }

    private function doCopyImage(object $entity): void
    {
        if (!$entity instanceof Song) {
            return;
        }

        $objId = \spl_object_id($entity);

        if (!isset($this->pendingSongs[$objId])) {
            return;
        }

        $pendingData = $this->pendingSongs[$objId];
        unset($this->pendingSongs[$objId]);

        $mapping = $pendingData['mapping'];

        $filepath = $mapping->getUploadDestination() . '/' . $mapping->getFileName($entity);
        if (!$this->fs->exists($filepath)) {
            return;
        }

        $outPath = Path::join($this->wipLocation, \sprintf('%s', $entity->getId()), 'cover.jpg');
        if (!$this->fs->exists(\dirname($outPath))) {
            $this->fs->mkdir(\dirname($outPath), 0755);
        }

        if ($this->fs->exists($outPath)){
            $this->fs->remove($outPath);
        }

        $this->fs->copy(
            $filepath,
            $outPath,
        );

        $entity->setCover(true);

        $this->emi->persist($entity);
        $this->emi->flush();
    }

    public function postPersist(Song $song): void
    {
        $this->doCopyImage($song);
    }

    public function postUpdate(Song $song): void
    {
        $this->doCopyImage($song);
    }
}
