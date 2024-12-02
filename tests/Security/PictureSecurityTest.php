<?php

namespace App\Tests\Security;

use App\Tests\AuthenticatedTestCase;

class PictureSecurityTest extends AuthenticatedTestCase
{
    /**
     * @TODO:
     * - Post picture in event + unauthenticated = 401
     * - Post picture in event + not participant = 403
     * - Post picture in event + participant = 403
     * - Post picture in event + admin = 403
     * - Post picture in event + appliance in event = 201
     * - Post picture in event + appliance not in event = 403
     */

    public function test_picture_getitem_unauthenticated(): void
    {
        $this->getUnauthenticated('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2', 401);
    }

    public function test_picture_getitem_not_participant(): void
    {
        $this->getUser('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2', 404, 'noevents');
    }

    public function test_picture_getitem_participant(): void
    {
        $this->getUser('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2', 200, 'user');
    }

    public function test_picture_getitem_owner(): void
    {
        // @TODO: Should not be an admin, just the owner
        $this->getUser('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2', 200, 'admin');
    }

    public function test_picture_getitem_appliance_in_event(): void
    {
        $this->getAppliance('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2', 200);
    }

    public function test_picture_getitem_appliance_not_in_event(): void
    {
        $this->getAppliance('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2', 403, self::APPLIANCE_NOT_OWNER_KEY);
    }

    public function test_picture_getcollection_unauthenticated(): void
    {
        $this->getUnauthenticated('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures', 401);
    }

    public function test_picture_getcollection_not_in_event(): void
    {
        $this->getUser('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures', 403, 'noevents');
    }

    public function test_picture_getcollection_in_event(): void
    {
        $token = $this->authenticate('user', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(5, $data['totalItems']);

        foreach($data['member'] as $picture) {
            $this->assertEquals(false, $picture['unattended']);
        }
    }

    public function test_picture_getcollection_in_event_unattended(): void
    {
        $token = $this->authenticate('user', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures?unattended=true', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(0, $data['totalItems']);
    }

    public function test_picture_getcollection_owner(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(14, $data['totalItems']);
    }

    public function test_picture_getcollection_owner_handtaken(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures?unattended=false', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(5, $data['totalItems']);

        foreach($data['member'] as $picture) {
            $this->assertEquals(false, $picture['unattended']);
        }
    }

    public function test_picture_getcollection_owner_unattended(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures?unattended=true', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(9, $data['totalItems']);

        foreach($data['member'] as $picture) {
            $this->assertEquals(true, $picture['unattended']);
        }
    }

    public function test_picture_getcollection_appliance_not_in_event(): void
    {
        $this->getAppliance('/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures', 403, self::APPLIANCE_NOT_OWNER_KEY);
    }

    public function test_picture_getcollection_appliance_in_event(): void
    {
        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(14, $data['totalItems']);
    }
}
