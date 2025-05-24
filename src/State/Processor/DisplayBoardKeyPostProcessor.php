<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DisplayBoardKey;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements ProcessorInterface<DisplayBoardKey, DisplayBoardKey>
 */
readonly class DisplayBoardKeyPostProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DisplayBoardKey
    {
        if (!$data instanceof DisplayBoardKey || !$operation instanceof Post) {
            return $data;
        }

        $data->setKey(\bin2hex(\random_bytes(32)));

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
