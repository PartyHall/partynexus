<?php

namespace App\Controller;

use App\Entity\Song;
use App\Enum\SongFormat;
use App\Exception\ProblemDetailsException;
use App\Repository\SongRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class SongUploadFileController extends AbstractController
{
    public function __construct(
        private readonly Security               $security,
        private readonly EntityManagerInterface $emi,
        private readonly SongRepository         $songRepository,
        private readonly Filesystem             $fs,
        private readonly SerializerInterface    $serializer,
        #[Autowire(env: 'SONG_EXTRACT_LOCATION')]
        private readonly string                 $wipLocation,
    )
    {
    }

    #[Route(
        path: '/api/songs/{id}/upload-file/{filetype}',
        methods: ['POST'],
    )]
    public function __invoke(Request $request, string $id, string $filetype): JsonResponse
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return new JsonResponse([], status: 403);
        }

        if (!\in_array($filetype, Song::$ALLOWED_FILETYPES, true)) {
            return new JsonResponse(['error' => 'Invalid filetype'], status: 400);
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if (!$file) {
            throw new ProblemDetailsException(400, 'Invalid file', 'No file provided');
        }

        $song = $this->songRepository->find($id);
        if (!$song instanceof Song) {
            throw new ProblemDetailsException(404, 'Song not found', 'The song you are trying to upload a file for does not exist');
        }

        if ($song->isReady()) {
            throw new ProblemDetailsException(400, 'Song already compiled', 'The song you are trying to upload a file for is already compiled');
        }

        if ('instrumental' === $filetype) {
            $outFile = Path::join($this->wipLocation, \sprintf('%s/instrumental.', $song->getId()));

            $this->fs->remove([
                $outFile . 'webm',
                $outFile . 'mp3',
            ]);

            if (SongFormat::CDG === $song->getFormat()) {
                $ext = 'mp3';
            } else {
                $ext = 'webm';
            }
        } elseif ('lyrics' === $filetype) {
            $ext = 'cdg';
            $outFile = Path::join($this->wipLocation, \sprintf('%s/lyrics.cdg', $song->getId()));
            $this->fs->remove($outFile);
        } else {
            $ext = 'mp3';
            $this->fs->remove(Path::join(
                $this->wipLocation,
                \sprintf(
                    '%s/%s.%s',
                    $song->getId(),
                    $filetype,
                    $ext,
                )
            ));
        }

        if ('vocals' === $filetype) {
            $song->setVocals(true);
        } elseif ('full' === $filetype) {
            $song->setCombined(true);
        }

        $this->emi->persist($song);
        $this->emi->flush();

        $file->move(
            Path::join($this->wipLocation, \sprintf('%s', $song->getId())),
            $filetype . '.' . $ext,
        );

        $songData = $this->serializer->serialize($song, 'json', [
            AbstractNormalizer::GROUPS => [Song::API_GET_ITEM],
        ]);

        return new JsonResponse($songData, json: true);
    }
}
