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

    // Get item but not authenticated
    public function test_picture_getitem_unauthenticated(): void
    {
        $resp = self::createClient()->request(
            'GET',
            \sprintf('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2'),
        );

        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Get item but not participant
    public function test_picture_getitem_not_participant(): void
    {
        $token = $this->authenticate('noevents', 'password');

        $resp = self::createClient()->request(
            'GET',
            \sprintf('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2'),
            ['headers' => ['Authorization' => $token]]
        );

        $this->assertEquals(404, $resp->getStatusCode());
    }

    // Get item and participant
    public function test_picture_getitem_participant(): void
    {
        $token = $this->authenticate('user', 'password');

        $resp = self::createClient()->request(
            'GET',
            \sprintf('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2'),
            ['headers' => ['Authorization' => $token]]
        );

        $this->assertEquals(200, $resp->getStatusCode());
    }

    // Get item and owner
    public function test_picture_getitem_owner(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = self::createClient()->request(
            'GET',
            \sprintf('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2'),
            ['headers' => ['Authorization' => $token]]
        );

        $this->assertEquals(200, $resp->getStatusCode());
    }

    // Get item appliance in event
    public function test_picture_getitem_appliance_in_event(): void
    {
        $resp = self::createClient()->request(
            'GET',
            \sprintf('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2'),
            [
                'headers' => [
                    'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                    'X-API-TOKEN' => self::APPLIANCE_SECRET,
                ],
            ],
        );

        $this->assertEquals(200, $resp->getStatusCode());
    }

    // Get item appliance not in event
    public function test_picture_getitem_appliance_not_in_event(): void
    {
        $resp = self::createClient()->request(
            'GET',
            \sprintf('/api/pictures/1019b299-d7c8-4670-aff3-9ebf6f9293d2'),
            [
                'headers' => [
                    'X-HARDWARE-ID' => self::APPLIANCE_NOT_OWNER_KEY,
                    'X-API-TOKEN' => self::APPLIANCE_NOT_OWNER_SECRET,
                ],
            ],
        );

        $this->assertEquals(403, $resp->getStatusCode());
    }

    public function test_picture_getcollection_unauthenticated(): void
    {
        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures');
        $this->assertEquals(401, $resp->getStatusCode());
    }

    public function test_picture_getcollection_not_in_event(): void
    {
        $token = $this->authenticate('noevents', 'password');

        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
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
        $resp = static::createClient()->request('GET', '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa/pictures', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_NOT_OWNER_KEY,
                'X-API-TOKEN' => self::APPLIANCE_NOT_OWNER_SECRET,
            ],
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
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
