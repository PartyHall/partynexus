<?php

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

readonly class BanUserProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $processor,
        private Security           $security,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User|Response
    {
        if ($data instanceof User){
            if ($this->security->getUser()->getUserIdentifier() === $data->getUserIdentifier()) {
                return new Response('You cannot ban / unban yourself', status: 400); // @TODO: Proper reponse
            }

            switch($operation->getName()) {
                case User::BAN_USER_ROUTE:
                    if (!$data->getBannedAt()) {
                        $data->setBannedAt(new \DateTimeImmutable());
                    }
                    break;
                case User::UNBAN_USER_ROUTE:
                    $data->setBannedAt(null);
                    break;
            }
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
