<?php

namespace App\Enum;

enum ExportStatus: string
{
    case STARTED = 'started';
    case ADDING_PICTURES = 'adding_pictures';
    case GENERATING_TIMELAPSE = 'generating_timelapse';

    case COMPLETE = 'complete';
}
