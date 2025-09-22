<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * /!\ POSTGRES SPECIFIC /!\
     * This should probably be rewritten with some doctrine ext or idk.
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

    public function findOneByOauthUserId(string $oauthUserId): ?UserInterface
    {
        return $this->findOneBy(['oauthUserId' => $oauthUserId]);
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.email) = LOWER(:query) OR LOWER(u.username) = LOWER(:query)')
            ->setParameter('query', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByUsername(string $username): ?UserInterface
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.username) = LOWER(:query)')
            ->setParameter('query', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
