<?php

namespace App\DataFixtures;

use ApiPlatform\Doctrine\Common\Filter\DateFilterInterface;
use App\Entity\Backdrop;
use App\Entity\BackdropAlbum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BackdropFixture extends Fixture implements DateFilterInterface
{
    private function addBackdrop(
        ObjectManager $manager,
        int $id,
        string $album,
        string $title,
    ): void {
        $src = __DIR__."/backdrop/$id.png";
        $src2 = "/tmp/$id.png";
        copy($src, $src2);

        $uploadedFile = new UploadedFile(
            path: $src2,
            originalName: "$id.png",
            mimeType: 'image/png',
            test: true,
        );

        $backdrop = (new Backdrop())
            ->setTitle($title)
            ->setAlbum($this->getReference($album, BackdropAlbum::class))
            ->setFile($uploadedFile)
        ;

        ReflectionUtils::setId($backdrop, $id);

        $manager->persist($backdrop);
        $manager->flush();

        $this->addReference("backdrop__$id", $backdrop);
    }

    public function load(ObjectManager $manager): void
    {
        $this->addBackdrop($manager, 1, 'backdropalbum__1', 'Picnic with friends');
        $this->addBackdrop($manager, 2, 'backdropalbum__1', 'Showing some meme');
        $this->addBackdrop($manager, 3, 'backdropalbum__1', 'Friend group');
        $this->addBackdrop($manager, 4, 'backdropalbum__2', 'Holiday picture');
        $this->addBackdrop($manager, 5, 'backdropalbum__2', 'With a friend');
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            BackdropAlbumFixture::class,
        ];
    }
}
