<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Appliance;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

// https://api-platform.com/docs/core/extensions/
final readonly class FilterEventOnOwnerExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $qb, string $resourceClass): void
    {
        if (
            (Event::class !== $resourceClass)
            || null === ($user = $this->security->getUser())
            || $this->security->isGranted('ROLE_ADMIN')
        ) {
            return;
        }

        $rootAlias = $qb->getRootAliases()[0];

        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->eq("$rootAlias.owner", ':current_user'),
                $qb->expr()->isMemberOf(':current_user', "$rootAlias.participants"),
            )
        );

        if ($user instanceof User) {
            $qb->setParameter('current_user', $user);
        } else if ($user instanceof Appliance) {
            $qb->setParameter('current_user', $user->getOwner());
        }

        $qb->orderBy("$rootAlias.datetime", "DESC");
    }
}
