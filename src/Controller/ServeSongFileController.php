<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
readonly class ServeSongFileController
{
    public function __construct(
        #[Autowire(env: 'SONG_EXTRACT_LOCATION')]
        private string $wipLocation,
    ) {
    }

    #[Route(
        path: '/api/song_file/{id}/{filename}',
        methods: ['GET'],
    )]
    public function __invoke(Request $request, string $id, string $filename): Response
    {
        if (!\in_array($filename, [
            'instrumental.webm',
            'instrumental.mp3',
            'vocals.mp3',
            'full.mp3',
            'lyrics.cdg',
        ])) {
            throw new BadRequestException();
        }

        $filepath = Path::join($this->wipLocation, $id, $filename);

        if (!\file_exists($filepath)) {
            throw new NotFoundHttpException();
        }

        return new BinaryFileResponse($filepath);
    }
}
