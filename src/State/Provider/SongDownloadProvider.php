<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Event;
use App\Entity\Song;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\SongRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @implements ProviderInterface<Response>
 */
readonly class SongDownloadProvider implements ProviderInterface
{
    public function __construct(
        private SongRepository $repo,
        private Security $security,
        #[Autowire(env: 'SONG_LOCATION')]
        private string $compiledLocation,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $id = $uriVariables['id'] ?? null;

        /** @var Song|null $song */
        $song = $this->repo->find($id);
        if (!$song) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
        }

        if (
            !(
                $this->security->isGranted('ROLE_ADMIN')
                || $this->security->isGranted('ROLE_APPLIANCE')
            )
            || !$song->isReady()
        ) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN);
        }

        $songPhk = join('/', [$this->compiledLocation, \sprintf('%s.phk', $song->getId())]);

        if (!file_exists($songPhk)) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
        }

        return new BinaryFileResponse($songPhk);
    }
}
