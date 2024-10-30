<?php

namespace App\Repository;

use App\Entity\Appliance;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findParticipatingEventsQuery(QueryBuilder $qb, User|Appliance $user): QueryBuilder
    {
        if ($user instanceof Appliance) {
            $user = $user->getOwner();
        }

        $rootAlias = $qb->getRootAliases()[0];

        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->eq("$rootAlias.owner", ':current_user'),
                $qb->expr()->isMemberOf(':current_user', "$rootAlias.participants"),
            )
        );

        $qb->setParameter('current_user', $user);

        return $qb;
    }
}
