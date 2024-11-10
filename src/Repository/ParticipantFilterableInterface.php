<?php

namespace App\Repository;

use App\Entity\Appliance;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

interface ParticipantFilterableInterface
{
    public function filterParticipatingEvents(QueryBuilder $qb, User|Appliance $user): QueryBuilder;
}
