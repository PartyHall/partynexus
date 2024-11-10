<?php

namespace App\Interface;

use App\Entity\Event;

/**
 * This is used for security
 * Any ApiResource that implements this will check whether the user
 * has the right to access.
 *
 * Which is: is admin or is owner or is participant
 */
interface HasEvent
{
    public function getEvent(): Event;
}
