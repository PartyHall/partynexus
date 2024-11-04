<?php

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use App\Entity\Appliance;
use App\Entity\Picture;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class PictureProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $processor,
        private Security $security,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Picture
    {
        $user = $this->security->getUser();
        if (!$data instanceof Picture || !$user instanceof Appliance) {
            throw new \Exception('This processor only supports instances of Picture sent from Appliances');
        }

        if ($data->getEvent()->getOwner() !== $user->getOwner()) {
            throw new AccessDeniedException();
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
