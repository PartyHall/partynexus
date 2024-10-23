<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class EventFixture extends Fixture implements DependentFixtureInterface
{
    /** @returns string[] */
    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            ApplianceFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();

        $event = (new Event())
            ->setName($faker->words(3, true))
            ->setAuthor($faker->name())
            ->setLocation($faker->address())
            ->setDatetime(\DateTimeImmutable::createFromMutable($faker->dateTime()))
            ->setOwner($this->getReference('user__admin'));

        $manager->persist($event);
        $this->addReference('event__1', $event);

        for ($i = 0; $i < 100; $i++) {
            $event = (new Event())
                ->setName($faker->words(3, true))
                ->setAuthor($faker->name())
                ->setLocation($faker->address())
                ->setDatetime(\DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setOwner($this->getReference('user__user'));

            $manager->persist($event);
            $this->addReference('event__user__' . ($i + 2), $event);
        }

        $manager->flush();

    }
}
