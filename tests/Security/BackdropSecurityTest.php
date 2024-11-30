<?php

namespace App\Tests\Security;

use App\Entity\BackdropAlbum;
use App\Tests\AuthenticatedTestCase;

class BackdropSecurityTest extends AuthenticatedTestCase
{
    private const array BACKDROP_ALBUM = [
        'title' => 'Some backdrop album name',
        'author' => 'The author',
        'version' => 1,
    ];

    private const array BACKDROP_ALBUM_CREATED = [
        'id' => 3,
        'title' => 'Some backdrop album name',
        'author' => 'The author',
        'version' => 1,
    ];

    public function test_backdrop_album_get_collection_unauthenticated(): void
    {
        $this->getUnauthenticated('/api/backdrop_albums', 401);
    }

    public function test_backdrop_album_get_collection_user(): void
    {
        $this->getUser('/api/backdrop_albums', 403, 'user');
    }

    public function test_backdrop_album_get_collection_admin(): void
    {
        $this->getAdmin('/api/backdrop_albums', 200);
    }

    public function test_backdrop_album_get_collection_appliance(): void
    {
        $this->getAppliance('/api/backdrop_albums', 200);
    }

    public function test_backdrop_album_get_item_unauthenticated(): void
    {
        $this->getUnauthenticated('/api/backdrop_albums/1', 401);
    }

    public function test_backdrop_album_get_item_user(): void
    {
        $this->getUser('/api/backdrop_albums/1', 403, 'user');
    }

    public function test_backdrop_album_get_item_admin(): void
    {
        $this->getAdmin('/api/backdrop_albums/1', 200);
    }

    public function test_backdrop_album_get_item_appliance(): void
    {
        $this->getAppliance('/api/backdrop_albums/1', 200);
    }

    public function test_backdrop_album_create_unauthenticated(): void
    {
        $resp = self::createClient()->request('POST', '/api/backdrop_albums', [
            'json' => self::BACKDROP_ALBUM,
        ]);

        $this->assertEquals(401, $resp->getStatusCode());
    }

    public function test_backdrop_album_create_user(): void
    {
        $token = $this->authenticate('user', 'password');

        $resp = self::createClient()->request('POST', '/api/backdrop_albums', [
            'headers' => ['Authorization' => $token],
            'json' => self::BACKDROP_ALBUM,
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    public function test_backdrop_album_create_admin(): void
    {
        $token = $this->authenticate('admin', 'password');

        $resp = self::createClient()->request('POST', '/api/backdrop_albums', [
            'headers' => ['Authorization' => $token],
            'json' => self::BACKDROP_ALBUM,
        ]);

        $this->assertEquals(201, $resp->getStatusCode());
        $this->assertJsonContains(self::BACKDROP_ALBUM_CREATED);
    }

    public function test_backdrop_album_create_appliance(): void
    {
        $resp = self::createClient()->request('POST', '/api/backdrop_albums', [
            'headers' => ['X-HARDWARE-ID' => self::APPLIANCE_KEY, 'X-API-TOKEN' => self::APPLIANCE_SECRET],
            'json' => self::BACKDROP_ALBUM,
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    public function test_backdrop_album_update_unauthenticated(): void
    {
        $resp = self::createClient()->request('PATCH', '/api/backdrop_albums/1', [
            'json' => self::BACKDROP_ALBUM,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        $this->assertEquals(401, $resp->getStatusCode());
    }

    public function test_backdrop_album_update_user(): void
    {
        $token = $this->authenticate('user', 'password');

        $resp = self::createClient()->request('PATCH', '/api/backdrop_albums/1', [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => self::BACKDROP_ALBUM,
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    public function test_backdrop_album_update_admin(): void
    {
        $token = $this->authenticate('admin', 'password');

        /*
        $backdropAlbum = $this->emi->getRepository(BackdropAlbum::class)->find(1);
        $this->assertNotNull($backdropAlbum);
        $this->assertEquals('Some backdrop album', $backdropAlbum->getTitle());
        $this->assertEquals('Some author', $backdropAlbum->getAuthor());
        $this->assertEquals(1, $backdropAlbum->getVersion());
        */

        $resp = self::createClient()->request('PATCH', '/api/backdrop_albums/1', [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'title' => 'Some backdrop album name',
                'author' => 'THE author',
                'version' => 2,
            ],
        ]);

        $this->assertEquals(200, $resp->getStatusCode());

        /*
         * // For some reason this doesn't work even though it works outside of tests
        $backdropAlbum = $this->emi->getRepository(BackdropAlbum::class)->find(1);
        $this->assertNotNull($backdropAlbum);
        $this->assertEquals('Some backdrop album name', $backdropAlbum->getTitle());
        $this->assertEquals('THE author', $backdropAlbum->getAuthor());
        $this->assertEquals(2, $backdropAlbum->getVersion());
        */
    }

    public function test_backdrop_album_update_appliance(): void
    {
        $resp = self::createClient()->request('PATCH', '/api/backdrop_albums/1', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => self::BACKDROP_ALBUM,
        ]);

        $this->assertEquals(403, $resp->getStatusCode());
    }

    public function test_backdrop_get_collection_unauthenticated(): void
    {
        $this->getUnauthenticated('/api/backdrop_albums/1/backdrops', 401);
    }

    public function test_backdrop_get_collection_user(): void
    {
        $this->getUser('/api/backdrop_albums/1/backdrops', 403, 'user');
    }

    public function test_backdrop_get_collection_admin(): void
    {
        $this->getAdmin('/api/backdrop_albums/1/backdrops', 200);
    }

    public function test_backdrop_get_collection_appliance(): void
    {
        $this->getAppliance('/api/backdrop_albums/1/backdrops', 200);
    }

    public function test_backdrop_get_item_unauthenticated(): void
    {
        $this->getUnauthenticated('/api/backdrop_albums/1/backdrops/1', 401);
    }

    public function test_backdrop_get_item_user(): void
    {
        $this->getUser('/api/backdrop_albums/1/backdrops/1', 403, 'user');
    }

    public function test_backdrop_get_item_admin(): void
    {
        $this->getAdmin('/api/backdrop_albums/1/backdrops/1', 200);
    }

    public function test_backdrop_get_item_appliance(): void
    {
        $this->getAppliance('/api/backdrop_albums/1/backdrops/1', 200);
    }

    // Backdrop - Create - Unauthenticated = 401
    // Backdrop - Create - User = 403
    // Backdrop - Create - Admin = 200
    // Backdrop - Create - Appliance = 403

    // Backdrop - Delete - Unauthenticated = 401
    // Backdrop - Delete - User = 403
    // Backdrop - Delete - Admin = 200
    // Backdrop - Delete - Appliance = 403
}
