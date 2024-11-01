<?php

namespace App\Enum;

enum ExportStatus: string
{
    case STARTED = 'started';
    case COMPLETE = 'complete';
    case FAILED = 'failed';
}
