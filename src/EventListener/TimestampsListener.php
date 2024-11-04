<?php

namespace App\EventListener;

use App\Interface\HasTimestamps;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(Events::prePersist)]
#[AsDoctrineListener(Events::preUpdate)]
class TimestampsListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $obj = $args->getObject();
        if (!$obj instanceof HasTimestamps) {
            return;
        }

        $obj->setCreatedAt(new \DateTimeImmutable());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $obj = $args->getObject();
        if (!$obj instanceof HasTimestamps) {
            return;
        }

        $obj->setUpdatedAt(new \DateTimeImmutable());
    }
}
