<?php

namespace App\Enum;

enum SongFileType: string
{
    case INSTRUMENTAL = 'instrumental';
    case VOCALS = 'vocals'; // Voices only
    case FULL = 'full'; // Instrumental+voices
    case CDG = 'cdg';
    case VIDEO = 'video'; // The video should contain instrumental + lyrics
}
