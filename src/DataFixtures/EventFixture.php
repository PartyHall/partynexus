<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\Uid\Uuid;

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

        ReflectionUtils::setId($event, Uuid::fromString('0192bf5a-67d8-7d9d-8a5e-962b23aceeaa'));

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
