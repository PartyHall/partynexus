<?php

namespace App\Utils;

class MediaUtils
{
    /**
     * @throws \Exception when the file is not available
     */
    public static function getMediaDuration(string $path): int|false
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new \Exception("File does not exist or is not readable: $path");
        }

        exec("ffprobe -v quiet -print_format json -show_format $path", $output, $returnCode);
        if (0 !== $returnCode) {
            return false;
        }

        $json = json_decode(implode('', $output), true);

        if (!isset($json['format']['duration'])) {
            return false;
        }

        return (int) floor((float) $json['format']['duration']);
    }
}
