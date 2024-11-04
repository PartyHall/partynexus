<?php

namespace App\Tests\Security;

use App\Entity\Event;
use App\Tests\AuthenticatedTestCase;
use Doctrine\ORM\EntityManagerInterface;

class DownloadsTest extends AuthenticatedTestCase
{
    private EntityManagerInterface $emi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emi = $this->getContainer()->get(EntityManagerInterface::class);
    }

    // Download picture (unauthenticated)
    public function test_download_picture_unauthenticated()
    {
        $resp = static::createClient()->request('GET', '/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2/download');
        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Download picture (Not participant)
    public function test_download_picture_not_participant()
    {
        $token = $this->authenticate('noevents', 'password');

        $resp = static::createClient()->request('GET', '/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2/download', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Download picture (Participant)
    public function test_download_picture_participant()
    {
        $token = $this->authenticate('user', 'password');

        $resp = static::createClient()->request('GET', '/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2/download', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(200, $resp->getStatusCode());
    }

    // Download picture (Owner)
    public function test_download_picture_owner()
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2/download', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(200, $resp->getStatusCode());
    }

    // Download timelapse (unauthenticated)
    public function test_download_timelapse_unauthenticated()
    {
        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/timelapse');

        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Download timelapse (Not participant)
    public function test_download_timelapse_not_participant()
    {
        $token = $this->authenticate('noevents', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/timelapse', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Download timelapse (Participant)
    public function test_download_timelapse_participant()
    {
        $token = $this->authenticate('user', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/timelapse', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(200, $resp->getStatusCode());
    }

    // Download timelapse (Owner)
    public function test_download_timelapse_owner()
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/timelapse', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(200, $resp->getStatusCode());
    }

    // Download export (unauthenticated)
    public function test_download_export_unauthenticated()
    {
        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/export');

        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Download export (Not participant)
    public function test_download_export_not_participant()
    {
        $token = $this->authenticate('noevents', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/export', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Download export (Participant)
    public function test_download_export_participant()
    {
        $token = $this->authenticate('user', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/export', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(200, $resp->getStatusCode());
    }

    // Download export (Owner)
    public function test_download_export_owner()
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/export', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(200, $resp->getStatusCode());
    }
}