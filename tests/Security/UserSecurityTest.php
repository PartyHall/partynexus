<?php

namespace App\Tests\Security;

use App\Tests\AuthenticatedTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserSecurityTest extends AuthenticatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // Get collection appliance (403)
    public function test_user_getcollection_appliance(): void
    {
        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ]
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    // Get item appliance (403)
    public function test_user_get_appliance(): void
    {
        $response = static::createClient()->request('GET', '/api/users/3', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ]
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    // Test get collection admin (200)
    public function test_user_getcollection_admin(): void
    {
        $token = $this->authenticate('admin', 'password');

        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        // @TODO: check that the data does not contains stuff that we don't want (e.g. password)
    }

    // Test get collection not admin (403)
    public function test_user_getcollection_user(): void
    {
        $token = $this->authenticate('user', 'password');

        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    // Test get item admin (200)
    public function test_user_get_admin(): void
    {
        $token = $this->authenticate('admin', 'password');

        $response = static::createClient()->request('GET', '/api/users/2', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertJsonContains([
            '@id' => '/api/users/2',
            'id' => 2,
            'username' => 'eventmaker',
            'email' => 'eventmaker@partyhall.dev',
        ]);

        $this->assertArrayNotHasKey('password', $response->toArray());
    }

    // Test get item self (200)
    public function test_user_get_self(): void
    {
        $token = $this->authenticate('eventmaker', 'password');

        $response = static::createClient()->request('GET', '/api/users/2', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertJsonContains([
            '@id' => '/api/users/2',
            'id' => 2,
            'username' => 'eventmaker',
            'email' => 'eventmaker@partyhall.dev',
        ]);

        $this->assertArrayNotHasKey('password', $response->toArray());
    }

    // Test get item not self (403)
    public function test_user_get_not_self(): void
    {
        $token = $this->authenticate('user', 'password');

        $response = static::createClient()->request('GET', '/api/users/2', [
            'headers' => ['Authorization' => $token]
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }
}
