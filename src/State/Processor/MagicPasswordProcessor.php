<?php

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\MagicPassword;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<MagicPassword, MagicPassword>
 */
class MagicPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<object, object> $processor */
        #[Autowire(service: PersistProcessor::class)]
        private readonly ProcessorInterface $processor,
    ) {
    }

    /**
     * @param MagicPassword $data
     * @param array<mixed>  $uriVariables
     * @param array<mixed>  $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MagicPassword
    {
        if (!$data instanceof MagicPassword) {
            throw new \InvalidArgumentException('MagicPasswordProcessor must be used on MagicPassword only');
        }

        if (!$operation instanceof Post) {
            throw new \InvalidArgumentException('MagicPasswordProcessor must be used on POST only');
        }

        $data->setCode(\bin2hex(\random_bytes(64)));
        $data->setUsed(false);

        return $this->processor->process($data, $operation, $context);
    }
}
