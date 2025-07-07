<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
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

        /** @var ClassMetadata<Event> $metadata */
        $metadata = $manager->getClassMetaData(Event::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
        $manager->flush();

        $event = (new Event())
            ->setName('My event')
            ->setAuthor('Me')
            ->setLocation('At home')
            ->setDatetime(new \DateTimeImmutable('2024-10-25T15:34:54Z'))
            ->setOwner($this->getReference('user__admin', User::class));

        ReflectionUtils::setId($event, Uuid::fromString('0192bf5a-67d8-7d9d-8a5e-962b23aceeaa'));

        $this->addReference('event__1', $event);

        $manager->persist($event);
        $manager->flush();

        $event->setParticipants([
            $this->getReference('user__eventmaker', User::class),
            $this->getReference('user__user', User::class),
        ]);

        $manager->persist($event);
        $manager->flush();

        /** @var ClassMetadata<Event> $metadata */
        $metadata = $manager->getClassMetaData(Event::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_CUSTOM);
        $metadata->setIdGenerator(new UuidGenerator());
        $manager->flush();

        for ($i = 0; $i < 100; ++$i) {
            /** @var string $name */
            $name = $faker->words(3, true); // phpstan bs

            $event = (new Event())
                ->setName($name)
                ->setAuthor($faker->name())
                ->setLocation($faker->address())
                ->setDatetime(\DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setOwner($this->getReference('user__user', User::class));

            $manager->persist($event);
            $this->addReference('event__user__'.($i + 2), $event);
        }

        $manager->flush();
    }
}
