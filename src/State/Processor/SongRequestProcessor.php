<?php

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\SongRequest;
use App\Entity\User;
use App\Message\NewSongRequestNotification;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class SongRequestProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<object, object> $processor */
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $processor,
        private Security           $security,
        private MessageBusInterface $messageBus,
    )
    {
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array<mixed> $uriVariables
     * @param array<mixed> $context
     * @throws \Exception
     * @return object
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object
    {
        if (!$data instanceof SongRequest){
            throw new \Exception('SongRequestProcessor should only be used on a song request');
        }

        $user = $this->security->getUser();
        if (!$user instanceof User){
            throw new \Exception('Only a user can create songrequests');
        }

        $data->setRequestedBy($user);

        $this->messageBus->dispatch(new NewSongRequestNotification(
           $data->getTitle(),
           $data->getArtist(),
           $user->getUsername(),
        ));

        return $this->processor->process(
            $data,
            $operation,
            $uriVariables,
            $context,
        );
    }
}
