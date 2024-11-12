<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Song;
use App\Repository\SongRepository;
use Psr\Log\LoggerInterface;
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
        private LoggerInterface $logger,
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
            $this->logger->error('Download song: Failed to find song in database');
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
            $this->logger->error('Download song: The file does not exists '.$songPhk);
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
        }

        return new BinaryFileResponse($songPhk);
    }
}
