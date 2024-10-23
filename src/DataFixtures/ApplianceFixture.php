<?php

namespace App\DataFixtures;

use App\Entity\Appliance;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class ApplianceFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $appliance = (new Appliance())
            ->setName('My Admin Appliance')
            ->setHardwareId(Uuid::fromString('b094786e-5158-4ceb-861b-28cb45b2a2c3'))
            ->setApiToken('my-api-token')
            ->setOwner($this->getReference('user__admin'))
        ;

        $manager->persist($appliance);
        $this->setReference('appliance__1', $appliance);

        $appliance = (new Appliance())
            ->setName('My User Appliance')
            ->setHardwareId(Uuid::fromString('c105897f-6169-5dfc-962c-39dc56c3b3d4'))
            ->setApiToken('my-api-token2')
            ->setOwner($this->getReference('user__user'))
        ;

        $manager->persist($appliance);
        $this->setReference('appliance__2', $appliance);

        $manager->flush();
    }

    /** @return string[] */
    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
