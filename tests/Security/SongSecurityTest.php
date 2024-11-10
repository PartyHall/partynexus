<?php

namespace App\Tests\Security;

use App\Tests\AuthenticatedTestCase;

class SongSecurityTest extends AuthenticatedTestCase
{
    // 1, 2, 4 => Ready
    // 3, 5 => Not ready

    private const array SONG_CREATE = [
        'title' => 'Some title',
        'artist' => 'Some artist',
        'format' => 'cdg',
        'quality' => 'ok',
        'hotspot' => 32,
    ];

    // Get all songs (Unauthenticated)
    public function test_get_songcollection_unauthenticated(): void
    {
        $resp = static::createClient()->request('GET', '/api/songs');
        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Get all songs (Authenticated)
    public function test_get_songcollection_authenticated(): void
    {
        $token = $this->authenticate('noevents', 'password');

        $resp = static::createClient()->request('GET', '/api/songs', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(3, $data['totalItems']);

        foreach($data['member'] as $song) {
            $this->assertEquals(true, $song['ready']);
        }
    }

    // Get all songs (Appliance)
    public function test_get_songcollection_appliance(): void
    {
        $resp = static::createClient()->request('GET', '/api/songs', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(3, $data['totalItems']);

        foreach($data['member'] as $song) {
            $this->assertEquals(true, $song['ready']);
        }
    }

    // Get all songs (Admin)
    public function test_get_songcollection_admin(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/songs', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(3, $data['totalItems']);
    }

    // Get ready songs (Admin)
    public function test_get_songcollection_admin_ready(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/songs?ready=true', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(3, $data['totalItems']);

        foreach($data['member'] as $song) {
            $this->assertEquals(true, $song['ready']);
        }
    }

    // Get not ready songs (Admin)
    public function test_get_songcollection_admin_not_ready(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/songs?ready=false', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        $data = json_decode($resp->getContent(), true);
        $this->assertEquals(152, $data['totalItems']);

        foreach($data['member'] as $song) {
            $this->assertEquals(false, $song['ready']);
        }
    }

    // Get one song (Unauthenticated, not ready)
    public function test_get_song_unauthenticated_not_ready(): void
    {
        $resp = static::createClient()->request('GET', '/api/songs/3');
        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Get one song (Unauthenticated, ready)
    public function test_get_song_unauthenticated_ready(): void
    {
        $resp = static::createClient()->request('GET', '/api/songs/1');
        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Get one song (Authenticated, not ready)
    public function test_get_song_authenticated_not_ready(): void
    {
        $token = $this->authenticate('user', 'password');

        $resp = static::createClient()->request('GET', '/api/songs/3', [
            'headers' => ['Authorization' => $token],
        ]);
        $this->assertEquals(404, $resp->getStatusCode());
    }

    // Get one song (Authenticated, ready)
    public function test_get_song_authenticated_ready(): void
    {
        $token = $this->authenticate('user', 'password');

        $resp = static::createClient()->request('GET', '/api/songs/1', [
            'headers' => ['Authorization' => $token],
        ]);
        $this->assertEquals(200, $resp->getStatusCode());
        // @TODO: check response
    }

    // Get one song (Appliance, not ready)
    public function test_get_song_appliance_not_ready(): void
    {
        $resp = static::createClient()->request('GET', '/api/songs/3', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
        ]);
        $this->assertEquals(404, $resp->getStatusCode());
    }

    // Get one song (Appliance, ready)
    public function test_get_song_appliance_ready(): void
    {
        $resp = static::createClient()->request('GET', '/api/songs/1', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
        ]);
        $this->assertEquals(200, $resp->getStatusCode());
        // @TODO: check response
    }

    // Get one song (Admin, not ready)
    public function test_get_song_admin_not_ready(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/songs/3', [
            'headers' => ['Authorization' => $token],
        ]);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    // Get one song (Admin, ready)
    public function test_get_song_admin_ready(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('GET', '/api/songs/1', [
            'headers' => ['Authorization' => $token],
        ]);
        $this->assertEquals(200, $resp->getStatusCode());
        // @TODO: check response
    }

    // Post one song (Unauthenticated)
    public function test_create_song_unauthenticated(): void
    {
        $resp = static::createClient()->request('POST', '/api/songs', [
            'json' => self::SONG_CREATE,
        ]);

        $this->assertEquals(401, $resp->getStatusCode());
    }

    // Post one song (Authenticated)
    public function test_create_song_authenticated(): void
    {
        $token = $this->authenticate('user', 'password');

        $resp = static::createClient()->request('POST', '/api/songs', [
            'headers' => ['Authorization' => $token],
            'json' => self::SONG_CREATE,
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Post one song (Appliance)
    public function test_create_song_appliance(): void
    {
        $resp = static::createClient()->request('POST', '/api/songs', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
            'json' => self::SONG_CREATE,
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Post one song (Admin)
    public function test_create_song_admin(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = static::createClient()->request('POST', '/api/songs', [
            'headers' => ['Authorization' => $token],
            'json' => self::SONG_CREATE,
        ]);

        $this->assertEquals(201, $resp->getStatusCode());
        $this->assertJsonContains(self::SONG_CREATE);
    }
}
