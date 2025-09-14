<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\SongSession;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SongSessionFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return [
            EventFixture::class,
            SongFixture::class,
            ApplianceFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $sessions = [
            (new SongSession())
                ->setTitle('Some song')
                ->setArtist('Some artist')
                ->setSinger('admin')
                ->setEvent($this->getReference('event__1', Event::class))
                ->setSungAt(new \DateTimeImmutable('-2 days')),
            (new SongSession())
                ->setTitle('Some other song')
                ->setArtist('Some other artist')
                ->setSinger('user')
                ->setSungAt(new \DateTimeImmutable('-1 days'))
                ->setEvent($this->getReference('event__1', Event::class)),
        ];

        foreach ($sessions as $s) {
            $manager->persist($s);
        }
        $manager->flush();
    }
}
