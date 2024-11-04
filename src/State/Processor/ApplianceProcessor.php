<?php

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Appliance;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<\App\Entity\Appliance, \App\Entity\Appliance>
 */
readonly class ApplianceProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<object, object> $processor */
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $processor,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Appliance && $operation instanceof Post) {
            if (($user = $this->security->getUser()) instanceof User) {
                $data->setOwner($user);
                $data->setApiToken(\bin2hex(\random_bytes(32)));
            } else {
                throw new BadRequestHttpException('Only user can create appliances');
            }
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
