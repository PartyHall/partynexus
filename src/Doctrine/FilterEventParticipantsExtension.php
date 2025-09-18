<?php

namespace App\Doctrine;

use App\Entity\Appliance;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class FilterEventParticipantsExtension extends AbstractPermissionsFilter
{
    public function __construct(
        Security $security,
        private EventRepository $eventRepository,
    ) {
        parent::__construct($security);
    }

    protected function getParamName(): string
    {
        return 'mine';
    }

    protected function getClassName(): string
    {
        return Event::class;
    }

    /**
     * @return array<class-string>
     */
    protected function getUserClassNames(): array
    {
        return [User::class, Appliance::class];
    }

    /**
     * @throws \Exception
     */
    protected function addCondition(QueryBuilder $queryBuilder, string $rootAlias): void
    {
        $user = $this->security->getUser();
        if ((!$user instanceof User) && (!$user instanceof Appliance)) {
            throw new \Exception('Invalid instance passed to addCondition');
        }

        $this->eventRepository
            ->filterParticipatingEvents($queryBuilder, $user)
            ->orderBy("$rootAlias.datetime", 'DESC');
    }
}
