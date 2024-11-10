<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Appliance;
use App\Entity\Picture;
use App\Entity\User;
use App\Repository\PictureRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class FilterPictureEventParticipantsExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private Security $security,
        private PictureRepository $repository,
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $query = $context['request']->query;

        $unattended = $query->has('unattended') ? 'true' === \strtolower($query->get('unattended')) : null;

        $this->addConditions($queryBuilder, $resourceClass, $unattended);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addConditions($queryBuilder, $resourceClass, null);
    }

    private function addConditions(QueryBuilder $queryBuilder, string $resourceClass, ?bool $unattended): void
    {
        $user = $this->security->getUser();

        if (
            Picture::class !== $resourceClass
            || (!$user instanceof User && !$user instanceof Appliance)
        ) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $this->repository
            ->filterParticipatingEvents($queryBuilder, $user)
            ->orderBy("$rootAlias.takenAt", 'ASC');

        if (!$this->security->isGranted('ROLE_ADMIN') && !$user instanceof Appliance) {
            $unattended = false;
        }

        if (null !== $unattended) {
            $queryBuilder->andWhere("$rootAlias.unattended = :unattended");
            $queryBuilder->setParameter('unattended', $unattended);
        }
    }
}
