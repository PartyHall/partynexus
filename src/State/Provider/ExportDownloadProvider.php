<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @implements ProviderInterface<Response>
 */
readonly class ExportDownloadProvider implements ProviderInterface
{
    public function __construct(
        private EventRepository $repo,
        private Security $security,
        #[Autowire(env: 'EXPORTS_LOCATION')]
        private string $basePath,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $id = $uriVariables['id'] ?? null;

        /** @var Event|null $event */
        $event = $this->repo->find($id);
        if (!$event) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        // @TODO: Do this properly
        // Maybe make a custom voter or so i'm not sure
        $isAllowed = true;
        if ($user !== $event->getOwner()) {
            $isAllowed = false;
        }

        if (!$isAllowed) {
            foreach ($event->getParticipants() as $participant) {
                if ($participant === $user) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        if (!$isAllowed) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN);
        }

        if (!$event->getExport()) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $timelapsePath = join('/', [$this->basePath, \sprintf('%s.zip', $event->getId()->toString())]);

        if (!file_exists($timelapsePath)) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
        }

        return new BinaryFileResponse($timelapsePath);
    }
}
