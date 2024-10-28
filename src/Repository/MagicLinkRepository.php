<?php

namespace App\Repository;

use App\Entity\MagicLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method findOneBy(array $criteria, ?array $orderBy = null): ?MagicLink
 * @extends ServiceEntityRepository<MagicLink>
 */
class MagicLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MagicLink::class);
    }

    public function findByEmailAndCode(string $email, string $code): ?MagicLink
    {
        return $this->createQueryBuilder('m')
            ->join('m.user', 'u')
            ->andWhere('u.email = :email')
            ->andWhere('m.code = :code')
            ->setParameter('email', $email)
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
