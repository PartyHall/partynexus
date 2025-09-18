<?php

namespace App\State\Provider\Pictures;

use Intervention\Image\Interfaces\ImageInterface;

readonly class PictureThumbnailProvider extends AbstractPictureDownloadProvider
{
    protected function getCacheDir(): string
    {
        return 'thumbnails';
    }

    protected function processPicture(ImageInterface $image): ImageInterface
    {
        return $image->cover(300, 300, 'center');
    }
}
