<?php

namespace App\State\Processor\ForgottenPassword;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ForgottenPassword;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<ForgottenPassword, ForgottenPassword>
 */
readonly class AdminForgottenPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<object, object> $processor */
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $processor,
    ) {
    }

    /**
     * @param ForgottenPassword $data
     * @param array<mixed>      $uriVariables
     * @param array<mixed>      $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ForgottenPassword
    {
        $data->setCode(\bin2hex(\random_bytes(64)));
        $data->setUsed(false);

        return $this->processor->process($data, $operation, $context);
    }
}
