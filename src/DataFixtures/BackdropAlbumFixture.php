<?php

namespace App\DataFixtures;

use App\Entity\BackdropAlbum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BackdropAlbumFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $album = (new BackdropAlbum())
            ->setTitle('Some backdrop album')
            ->setAuthor('Some author')
            ->setVersion(1)
        ;

        ReflectionUtils::setId($album, 1);

        $manager->persist($album);
        $this->addReference('backdropalbum__1', $album);

        $album = (new BackdropAlbum())
            ->setTitle('Another album')
            ->setAuthor('Some other author')
            ->setVersion(4)
        ;

        ReflectionUtils::setId($album, 2);

        $manager->persist($album);
        $this->addReference('backdropalbum__2', $album);

        $manager->flush();
    }
}
