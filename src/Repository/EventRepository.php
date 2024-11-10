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
class EventRepository extends ServiceEntityRepository implements ParticipantFilterableInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function filterParticipatingEvents(QueryBuilder $qb, User|Appliance $user): QueryBuilder
    {
        if ($user instanceof Appliance) {
            $user = $user->getOwner();
        }

        $rootAlias = $qb->getRootAliases()[0];

        // Required so that it works properly with getItem AND getCollection
        // As doctrine adds its filter on ID
        $existingWhere = $qb->getDQLPart('where');

        $newCondition = $qb->expr()->orX(
            $qb->expr()->eq("$rootAlias.owner", ':current_user'),
            ":current_user MEMBER OF $rootAlias.participants"
        );

        if ($existingWhere) {
            $qb->where($qb->expr()->andX($existingWhere, $newCondition));
        } else {
            $qb->where($newCondition);
        }

        $qb->setParameter('current_user', $user);

        return $qb;
    }
}
