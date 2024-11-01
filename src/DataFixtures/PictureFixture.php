<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Picture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class PictureFixture extends Fixture implements DependentFixtureInterface
{

    public function getDependencies(): array
    {
        return [
            EventFixture::class,
            ApplianceFixture::class,
        ];
    }

    private function addPicture(
        ObjectManager $manager,
        string $uuid,
        Event $event,
        string $filename,
        bool $unattended = false,
    ): void
    {
        $src = __DIR__ . '/pictures/' . $filename;
        $src2 = '/tmp/' .$filename;
        copy($src, $src2);

        $uploadedFile = new UploadedFile(
            path: $src2,
            originalName: $filename,
            mimeType: 'image/jpeg',
            test: true,
        );

        $picture = (new Picture())
            ->setEvent($event)
            ->setApplianceUuid(Uuid::v4())
            ->setUnattended($unattended)
            ->setTakenAt(\DateTimeImmutable::createFromFormat(
                \DateTimeInterface::ATOM,
                '2024-09-01T22:31:43Z',
            ))
            ->setFile($uploadedFile)
        ;

        ReflectionUtils::setId($picture, Uuid::fromString($uuid));

        $manager->persist($picture);
        $manager->flush();

        $this->addReference($filename, $picture);
    }

    public function load(ObjectManager $manager): void
    {
        $metadata = $manager->getClassMetaData(Picture::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
        $manager->flush();

        /** @var Event $event */
        $event = $this->getReference('event__1');

        // Handtaken pictures for event 1 (the admin one)
        $this->addPicture($manager, '1019b299-d7c8-4670-aff3-9ebf6f9293d2', $event, '1.jpg');
        $this->addPicture($manager, '8f0ce424-091c-4a33-b7b1-b7e82ae7d29e', $event, '2.jpg');
        $this->addPicture($manager, '354ccaa7-43d4-4fea-9d8c-7da5f3d995ca', $event, '3.jpg');
        $this->addPicture($manager, 'c827de7e-dea1-4995-b3d9-62195bedff16', $event, '4.jpg');
        $this->addPicture($manager, '9c97f5f4-fb67-4af2-875f-0830bf1e0f75', $event, '5.jpg');

        // Unattended pictures for event 1 (the admin one)
        $this->addPicture($manager, 'dad5e044-09d5-480c-a903-d3998e4ba421', $event, '6.jpg', true);
        $this->addPicture($manager, '9f63b7be-d89e-4e78-93c5-540ea9a9d5e7', $event, '7.jpg', true);
        $this->addPicture($manager, 'dcf1f98a-a319-4e74-8d71-089482d313ad', $event, '8.jpg', true);
        $this->addPicture($manager, '67aea042-e0b9-407e-8d6e-01620cd6ab34', $event, '9.jpg', true);
        $this->addPicture($manager, 'fabadd83-d48a-4e88-bbf4-4404a742ee06', $event, '10.jpg', true);
        $this->addPicture($manager, '70ce0675-e275-4f52-b144-0496e6be24b4', $event, '11.jpg', true);
        $this->addPicture($manager, 'c035787f-9766-4421-b6fe-3a77ee502442', $event, '12.jpg', true);
        $this->addPicture($manager, '0cbac9c4-13bb-4005-b1bd-5899317ba8a8', $event, '13.jpg', true);
        $this->addPicture($manager, '91e0a5e7-9cd6-45b4-a660-7226bffa5b4d', $event, '14.jpg', true);

    }
}
