<?php

namespace App\Tests\Security;

use App\Tests\AuthenticatedTestCase;

class DownloadsSecurityTest extends AuthenticatedTestCase
{
    public function test_download_picture_unauthenticated(): void
    {
        $this->getUnauthenticated('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2/download', 401);
    }

    public function test_download_picture_not_participant(): void
    {
        $this->getUser('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2/download', 403, 'noevents');
    }

    public function test_download_picture_participant(): void
    {
        $this->getUser('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2/download', 200, 'user');
    }

    public function test_download_picture_owner(): void
    {
        // @TODO: Should not be an admin, just the owner
        $this->getUser('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2/download', 200, 'admin');
    }

    public function test_download_picture_appliance_not_owner(): void
    {
        $this->getAppliance('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2/download', 403, self::APPLIANCE_NOT_OWNER_KEY);
    }

    public function test_download_picture_appliance_owner(): void
    {
        $this->getAppliance('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2/download', 403);
    }

    public function test_download_timelapse_unauthenticated(): void
    {
        $this->getUnauthenticated('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/timelapse', 401);
    }

    public function test_download_timelapse_not_participant(): void
    {
        $this->getUser('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/timelapse', 403, 'noevents');
    }

    public function test_download_timelapse_participant(): void
    {
        $this->getUser('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/timelapse', 200, 'user');
    }

    public function test_download_timelapse_owner(): void
    {
        // @TODO: Should not be an admin, just the owner
        $this->getUser('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/timelapse', 200, 'admin');
    }

    public function test_download_timelapse_appliance_not_owner(): void
    {
        $this->getAppliance('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/timelapse', 403, self::APPLIANCE_NOT_OWNER_KEY);
    }

    public function test_download_timelapse_appliance_owner(): void
    {
        $this->getAppliance('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/timelapse', 403);
    }

    public function test_download_export_unauthenticated(): void
    {
        $this->getUnauthenticated('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/export', 401);
    }

    public function test_download_export_not_participant(): void
    {
        $this->getUser('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/export', 403, 'noevents');
    }

    public function test_download_export_participant(): void
    {
        $this->getUser('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/export', 200, 'user');
    }

    public function test_download_export_owner(): void
    {
        // @TODO: Should not be an admin, just the owner
        $this->getUser('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/export', 200, 'admin');
    }

    public function test_download_export_appliance_not_owner(): void
    {
        $this->getAppliance('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/export', 403, self::APPLIANCE_NOT_OWNER_KEY);
    }

    public function test_download_export_appliance_owner(): void
    {
        $this->getAppliance('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/export',403);
    }
}
