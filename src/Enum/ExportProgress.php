<?php

namespace App\Enum;

enum ExportProgress: string
{
    case STARTED = 'started';
    case ADDING_PICTURES = 'adding_pictures';
    case GENERATING_TIMELAPSE = 'generating_timelapse';
    case ADDING_METADATA = 'adding_metadata';
    case BUILDING_ZIP = 'building_zip';
}
