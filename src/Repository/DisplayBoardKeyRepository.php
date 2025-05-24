<?php

namespace App\Repository;

use App\Entity\DisplayBoardKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DisplayBoardKey>
 */
class DisplayBoardKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DisplayBoardKey::class);
    }
}
