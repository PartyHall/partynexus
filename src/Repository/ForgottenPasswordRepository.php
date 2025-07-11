<?php

namespace App\Repository;

use App\Entity\ForgottenPassword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method findOneBy(array $criteria, ?array $orderBy = null): ?ForgottenPassword
 *
 * @extends ServiceEntityRepository<ForgottenPassword>
 */
class ForgottenPasswordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForgottenPassword::class);
    }

    public function findByCode(string $code): ?ForgottenPassword
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
