<?php

namespace App\State\Provider\Pictures;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\PictureRepository;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<BinaryFileResponse>
 */
abstract readonly class AbstractPictureDownloadProvider implements ProviderInterface
{
    public function __construct(
        private PictureRepository $pictureRepository,
        private LoggerInterface $logger,
        private Security $security,
        #[Autowire(env: 'PICTURES_LOCATION')]
        private string $rootPicturePath,
        #[Autowire(param: 'kernel.cache_dir')]
        private string $cacheDir,
        private ImageManager $imageManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $picture = $this->pictureRepository->find($uriVariables['id'] ?? null);
        if (!$picture) {
            return throw new NotFoundHttpException();
        }

        $user = $this->security->getUser();
        if (!$this->security->isGranted('ROLE_ADMIN') && !$picture->getEvent()->getParticipants()->contains($user)) {
            $this->logger->warning(
                'A user tried to access a picture without being a participant of the event.',
                [
                    'user' => $user?->getUserIdentifier(),
                    'event' => $picture->getEvent()->getId(),
                    'picture' => $picture->getId(),
                ]
            );

            throw new NotFoundHttpException();
        }

        $pictureFile = $picture->getFilepath();

        $cachePath = Path::join(
            $this->cacheDir,
            $this->getCacheDir(),
            $pictureFile,
        );

        if (!\file_exists($cachePath)) {
            $picturePath = Path::join(
                $this->rootPicturePath,
                $pictureFile,
            );

            if (!\file_exists($picturePath)) {
                $this->logger->error(
                    'Trying to thumbnail a picture that does not exist.',
                    ['picture' => $picture->getId(), 'file' => $pictureFile],
                );

                return throw new NotFoundHttpException();
            }

            if (!\is_dir(\dirname($cachePath))) {
                \mkdir(\dirname($cachePath), 0755, true);
            }

            $image = $this->processPicture($this->imageManager->read($picturePath));
            $image->toJpeg()->save($cachePath);
        }

        return new BinaryFileResponse($cachePath);
    }

    abstract protected function getCacheDir(): string;

    abstract protected function processPicture(ImageInterface $image): ImageInterface;
}
