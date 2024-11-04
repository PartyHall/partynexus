<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Appliance;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

// https://api-platform.com/docs/core/extensions/
final readonly class FilterForceParticipantsExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security,
        private EventRepository $eventRepository,
    ) {
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
            // || $this->security->isGranted('ROLE_ADMIN')
            || (!$user instanceof User && !$user instanceof Appliance)
        ) {
            return;
        }

        $rootAlias = $qb->getRootAliases()[0];

        $this->eventRepository
            ->findParticipatingEventsQuery($qb, $user)
            ->orderBy("$rootAlias.datetime", 'DESC');
    }
}
