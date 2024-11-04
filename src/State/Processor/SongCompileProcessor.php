<?php

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Song;
use App\Service\SongCompilator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class SongCompileProcessor implements ProcessorInterface
{
    public function __construct(
        private SongCompilator $compilator,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $processor,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof Song || !$operation instanceof Patch) {
            throw new \Exception('The SongCompileProcessor should only be used on Patch operation for song');
        }

        if (!$data->isReady()) {
            $this->compilator->compile($data);
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
