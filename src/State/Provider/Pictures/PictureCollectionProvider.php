<?php

namespace App\State\Provider\Pictures;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\DisplayBoardKey;
use App\Entity\Picture;
use App\Repository\EventRepository;
use App\Repository\PictureRepository;
use App\Security\EventVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProviderInterface<Picture>
 */
readonly class PictureCollectionProvider implements ProviderInterface
{
    /**
     * @param ProviderInterface<Picture> $decorated
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $decorated,
        private Security $security,
        private EventRepository $eventRepository,
        private PictureRepository $pictureRepository,
    ) {
    }

    /**
     * @param array<mixed> $uriVariables
     * @param array<mixed> $context
     *
     * @return Picture|array<Picture>|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|Picture|null
    {
        $eventId = $uriVariables['eventId'] ?? null;
        if ($eventId) {
            $user = $this->security->getUser();

            $event = $this->eventRepository->find($eventId);

            // If the event is not found, just deny the access
            if (!$event) {
                throw new AccessDeniedHttpException();
            }

            // If the user is a display board, we return a hardcoded doctrine query
            // to get the last X amount of pictures, bypassing any filters
            if ($user instanceof DisplayBoardKey) {
                // We ensure that the display board is the one for the event
                if ($user->getEvent() != $event) {
                    throw new AccessDeniedHttpException();
                }

                return $this->pictureRepository->findByDisplayBoard($event);
            }

            // Otherwise, we check that the user has access to the event
            if (!$this->security->isGranted(EventVoter::PARTICIPANT, $event)) {
                throw new AccessDeniedHttpException();
            }
        }

        // Once everything is good, we simply call the decorated provider
        return $this->decorated->provide($operation, $uriVariables, $context);
    }
}
