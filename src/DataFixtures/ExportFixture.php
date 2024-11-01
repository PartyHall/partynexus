<?php

namespace App\DataFixtures;

use App\Entity\Export;
use App\Enum\ExportProgress;
use App\Enum\ExportStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ExportFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            EventFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $event = $this->getReference('event__1');

        $export = (new Export())
            ->setEvent($event)
            ->setStartedAt(new \DateTimeImmutable('2024-09-01T01:00:00'))
            ->setEndedAt(new \DateTimeImmutable('2024-09-01T01:20:00'))
            ->setProgress(ExportProgress::BUILDING_ZIP)
            ->setStatus(ExportStatus::COMPLETE)
        ;

        $manager->persist($export);
        $manager->flush();
    }
}
