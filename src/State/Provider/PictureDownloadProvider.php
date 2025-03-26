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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @implements ProviderInterface<Response>
 */
readonly class PictureDownloadProvider implements ProviderInterface
{
    public function __construct(
        private PictureRepository $repo,
        private Security $security,
        private RequestStack $requestStack,
        #[Autowire(env: 'PICTURES_LOCATION')]
        private string $basePath,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $id = $uriVariables['id'] ?? null;
        $alternatePicture = 'true' === strtolower($this->requestStack->getMainRequest()->query->get('alternate'));

        /** @var Picture|null $picture */
        $picture = $this->repo->find($id);
        if (!$picture) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        // @TODO: Do this properly
        // Maybe make a custom voter or so i'm not sure
        $isAllowed = true;
        if ($user !== $picture->getEvent()->getOwner()) {
            $isAllowed = false;
        }

        if (!$isAllowed) {
            foreach ($picture->getEvent()->getParticipants() as $participant) {
                if ($participant === $user) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        if (!$isAllowed) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN);
        }

        $filepath = $picture->getFilepath();
        if ($alternatePicture) {
            if (!$picture->isHasAlternatePicture()) {
                throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
            }

            $filepath = $picture->getAlternateFilepath();
        }

        $picturePath = join('/', [$this->basePath, $filepath]);

        if (!file_exists($picturePath)) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
        }

        return new BinaryFileResponse($picturePath);
    }
}
