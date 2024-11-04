<?php

namespace App\Utils;

// https://stackoverflow.com/questions/1707801/making-a-temporary-dir-for-unpacking-a-zipfile-into
class DirectoryUtils
{
    /**
     * Creates a random unique temporary directory, with specified parameters,
     * that does not already exist (like tempnam(), but for dirs).
     *
     * Created dir will begin with the specified prefix, followed by random
     * numbers.
     *
     * @see https://php.net/manual/en/function.tempnam.php
     *
     * @param string|null $dir         Base directory under which to create temp dir.
     *                                 If null, the default system temp dir (sys_get_temp_dir()) will be
     *                                 used.
     * @param string      $prefix      string with which to prefix created dirs
     * @param int         $mode        Octal file permission mask for the newly-created dir.
     *                                 Should begin with a 0.
     * @param int         $maxAttempts maximum attempts before giving up (to prevent
     *                                 endless loops)
     *
     * @return string|bool full path to newly-created dir, or false on failure
     */
    public static function tempdir(?string $dir = null, string $prefix = 'tmp_', int $mode = 0700, int $maxAttempts = 1000): bool|string
    {
        /* Use the system temp dir by default. */
        if (is_null($dir)) {
            $dir = sys_get_temp_dir();
        }

        /* Trim trailing slashes from $dir. */
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        /* If we don't have permission to create a directory, fail, otherwise we will
         * be stuck in an endless loop.
         */
        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }

        /* Make sure characters in prefix are safe. */
        if (false !== strpbrk($prefix, '\\/:*?"<>|')) {
            return false;
        }

        /* Attempt to create a random directory until it works. Abort if we reach
         * $maxAttempts. Something screwy could be happening with the filesystem
         * and our loop could otherwise become endless.
         */
        $attempts = 0;
        do {
            $path = sprintf('%s%s%s%s', $dir, DIRECTORY_SEPARATOR, $prefix, mt_rand(100000, mt_getrandmax()));
        } while (
            !mkdir($path, $mode)
            && $attempts++ < $maxAttempts
        );

        return $path;
    }
}
