<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Picture;
use App\Repository\EventRepository;
use App\Security\EventVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProviderInterface<\App\Entity\Picture>
 */
readonly class PictureCollectionProvider implements ProviderInterface
{
    /**
     * @param ProviderInterface<\App\Entity\Picture> $decorated
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $decorated,
        private Security $security,
        private EventRepository $eventRepository,
    ) {
    }

    /**
     * @param array<mixed> $uriVariables
     * @param array<mixed> $context
     *
     * @return array<Picture>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $eventId = $uriVariables['eventId'] ?? null;
        if ($eventId) {
            $event = $this->eventRepository->find($eventId);
            if (!$event || !$this->security->isGranted(EventVoter::PARTICIPANT, $event)) {
                throw new AccessDeniedHttpException();
            }
        }

        return $this->decorated->provide($operation, $uriVariables, $context);
    }
}
