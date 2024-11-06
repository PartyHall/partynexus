<?php

namespace App\Tests\Security;

class SongSessionSecurityTest
{
    /**
     * @TODO:
     * - Not authenticated + get item = 401
     * - User not in event + get item = 403
     * - User in event + get item = 200
     * - Not authenticated + get collection = 401
     * - User not in event + get collection = 403
     * - User in event + get collection = 200
     */

    public function test_songsession_getitem_unauthenticated(): void
    {

    }
}
