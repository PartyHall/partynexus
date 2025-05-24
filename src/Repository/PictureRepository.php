<?php

namespace App\Repository;

use App\Entity\Appliance;
use App\Entity\Event;
use App\Entity\Picture;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Picture>
 */
class PictureRepository extends ServiceEntityRepository implements ParticipantFilterableInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Picture::class);
    }

    public function filterParticipatingEvents(QueryBuilder $qb, User|Appliance $user): QueryBuilder
    {
        if ($user instanceof Appliance) {
            $user = $user->getOwner();
        }

        $rootAlias = $qb->getRootAliases()[0];

        $qb->innerJoin("$rootAlias.event", 'event');

        // Required so that it works properly with getItem AND getCollection
        // As doctrine adds its filter on ID
        $existingWhere = $qb->getDQLPart('where');

        $newCondition = $qb->expr()->orX(
            $qb->expr()->eq('event.owner', ':current_user'),
            ':current_user MEMBER OF event.participants'
        );

        if ($existingWhere) {
            $qb->where($qb->expr()->andX($existingWhere, $newCondition));
        } else {
            $qb->where($newCondition);
        }

        $qb->setParameter('current_user', $user);

        return $qb;
    }

    public function findByDisplayBoard(Event $event): mixed
    {
        return $this
            ->createQueryBuilder('p')
            ->where('p.event = :event')
            ->andWhere('p.unattended = FALSE')
            ->setParameter('event', $event)
            ->orderBy('p.takenAt', 'DESC')
            ->setMaxResults(9)
            ->getQuery()
            ->getResult()
        ;
    }
}
