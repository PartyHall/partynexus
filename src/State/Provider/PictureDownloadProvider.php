<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Picture;
use App\Entity\User;
use App\Repository\PictureRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PictureDownloadProvider implements ProviderInterface
{
    public function __construct(
        private readonly PictureRepository $repo,
        private readonly Security $security,
        #[Autowire(env: 'PICTURE_LOCATIONS')]
        private readonly string $basePath,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $id = $uriVariables['id'] ?? null;

        /** @var Picture $picture */
        $picture = $this->repo->find($id);
        if (!$picture) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        // @TODO: Also allow user that are in the event
        // Maybe make a custom voter ?
        if ($user !== $picture->getEvent()->getOwner()) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN);
        }

        $picturePath = join('/', [$this->basePath, $picture->filepath]);

        if (!file_exists($picturePath)) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
        }

        return new BinaryFileResponse($picturePath);
    }
}
