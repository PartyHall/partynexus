<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * /!\ POSTGRES SPECIFIC /!\
     * This should probably be rewritten with some doctrine ext or idk
     *
     * @return array<User>
     */
    public function findByRole(string $role): array
    {
        $rsm = $this->createResultSetMappingBuilder('nu');

        $rawQuery = sprintf(
            'SELECT %s FROM nexus_user nu WHERE nu.roles::jsonb ?? :role',
            $rsm->generateSelectClause()
        );

        $query = $this->getEntityManager()->createNativeQuery($rawQuery, $rsm);
        $query->setParameter('role', $role);

        return $query->getResult();
    }
}
