<?php

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Song;
use App\Service\SongCompilator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<\App\Entity\Song, \App\Entity\Song>
 */
readonly class SongDecompileProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<object, object> $processor */
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $processor,
        private SongCompilator $compilator,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Song
    {
        if (!$data instanceof Song || !$operation instanceof Patch) {
            throw new \Exception('The SongCompileProcessor should only be used on Patch operation for song');
        }

        if ($data->isReady()) {
            $this->compilator->decompile($data);
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
