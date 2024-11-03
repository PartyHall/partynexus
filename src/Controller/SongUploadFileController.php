<?php

namespace App\Controller;

use App\Entity\Song;
use App\Enum\SongFormat;
use App\Repository\SongRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
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

        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'Invalid file provided'], status: 400);
        }

        $song = $this->songRepository->find($id);
        if (!$song instanceof Song) {
            return new JsonResponse(['error' => 'Song not found'], status: 404);
        }

        if ($song->isReady()) {
            return new JsonResponse(['error' => 'The song is compiled'], status: 400);
        }

        // The cover is a special case as it should be both in the
        // zip file and in the vich folder
        if ($filetype === 'cover') {
            $outdir = Path::join($this->wipLocation, \sprintf('%s/cover.jpg', $song->getId()));
            $this->fs->remove($outdir);

            $this->fs->copy(
                $file->getRealPath(),
                $outdir,
            );

            $song->setCoverFile($file);
            $song->setCover(true);

            $this->emi->persist($song);
            $this->emi->flush();
        } else {
            if ($filetype === 'instrumental') {
                $outFile = Path::join($this->wipLocation, \sprintf('%s/instrumental.', $song->getId()));

                $this->fs->remove([
                    $outFile . 'webm',
                    $outFile . 'mp3',
                ]);

                if ($song->getFormat() === SongFormat::CDG) {
                    $ext = 'mp3';
                } else {
                    $ext = 'webm';
                }
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

            if ($filetype === 'vocals') {
                $song->setVocals(true);
            } else if ($filetype === 'full') {
                $song->setCombined(true);
            }

            $this->emi->persist($song);
            $this->emi->flush();

            $file->move(
                Path::join($this->wipLocation, \sprintf('%s', $song->getId())),
                $filetype . '.' . $ext,
            );
        }

        $songData = $this->serializer->serialize($song, 'json', [
            'groups' => [Song::API_GET_ITEM],
        ]);

        return new JsonResponse($songData, json: true);
    }
}
