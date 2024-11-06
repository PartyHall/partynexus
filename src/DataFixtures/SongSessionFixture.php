<?php

namespace App\DataFixtures;

use App\Entity\SongSession;
use App\Repository\SongRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SongSessionFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private SongRepository $repository,
    )
    {
    }

    /**
     * @return \class-string[]
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
                ->setEvent($this->getReference('event__1'))
                ->setSungAt(new \DateTimeImmutable('-2 days'))
                ->setAppliaceId(1),
            (new SongSession())
                ->setTitle('Some other song')
                ->setArtist('Some other artist')
                ->setSinger('user')
                ->setSungAt(new \DateTimeImmutable('-1 days'))
                ->setSong($this->repository->findBy([
                    'title' => 'The backup',
                    'artist' => '2080',
                ])[0])
                ->setEvent($this->getReference('event__1'))
                ->setAppliaceId(2),
        ];

        foreach ($sessions as $s) {
            $manager->persist($s);
        }
        $manager->flush();
    }
}
