<?php

namespace App\Repository;

use App\Entity\MagicPassword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method findOneBy(array $criteria, ?array $orderBy = null): ?MagicPassword
 *
 * @extends ServiceEntityRepository<MagicPassword>
 */
class MagicPasswordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MagicPassword::class);
    }

    public function findByCode(string $code): ?MagicPassword
    {
        return $this->createQueryBuilder('m')
            ->join('m.user', 'u')
            ->andWhere('u.bannedAt IS NULL')
            ->andWhere('m.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
