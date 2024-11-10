<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Tests\AuthenticatedTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserSecurityTest extends AuthenticatedTestCase
{
    private EntityManagerInterface $emi;

    protected function setUp(): void
    {
        $this->emi = $this->getContainer()->get(EntityManagerInterface::class);
    }

    // Get collection unauthenticated (401)
    public function test_user_getcollection_unauthenticated(): void
    {
        $response = static::createClient()->request('GET', '/api/users', []);
        $this->assertEquals(401, $response->getStatusCode());
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


    // Get item unauthenticated (401)
    public function test_user_get_unauthenticated(): void
    {
        $response = static::createClient()->request('GET', '/api/users/3', []);
        $this->assertEquals(401, $response->getStatusCode());
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

    // Test create user unauthenticated
    public function test_user_register_unauthenticated(): void
    {
        $response = static::createClient()->request('POST', '/api/users', [
            'json' => [
                'username' => 'toto',
                'email' => 'toto@tutu.fr',
                'language' => 'en_US',
            ],
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    // Test create user by user
    public function test_user_register_user(): void
    {
        $token = $this->authenticate('user', 'password');

        $response = static::createClient()->request('POST', '/api/users', [
            'headers' => ['Authorization' => $token],
            'json' => [
                'username' => 'toto',
                'email' => 'toto@tutu.fr',
                'language' => 'en_US',
            ],
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    // Test create user by admin
    public function test_user_register_admin(): void
    {
        $token = $this->authenticate('admin', 'password');

        $response = static::createClient()->request('POST', '/api/users', [
            'headers' => ['Authorization' => $token],
            'json' => [
                'username' => 'toto',
                'email' => 'toto@tutu.fr',
                'language' => 'en_US',
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    // Test create user by appliance
    public function test_user_register_appliance(): void
    {
        $response = static::createClient()->request('POST', '/api/users', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
            'json' => [
                'username' => 'toto',
                'email' => 'toto@tutu.fr',
                'language' => 'en_US',
            ],
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    // Update user (unauthenticated)
    public function test_user_update_unauthenticated(): void
    {
        $response = static::createClient()->request('PATCH', '/api/users/2', [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'username' => 'toto',
                'email' => 'toto@tutu.fr',
                'language' => 'en_US',
            ],
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    // Update user (self)
    public function test_user_update_self(): void
    {
        $token = $this->authenticate('eventmaker', 'password');

        $response = static::createClient()->request('PATCH', '/api/users/2', [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'username' => 'toto',
                'email' => 'toto@tutu.fr',
                'language' => 'en_US',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        /** @var User|null $user */
        $user = $this->emi->getRepository(User::class)->find(2);
        $this->assertNotNull($user);

        $this->assertEquals('toto', $user->getUsername());
        $this->assertEquals('toto@tutu.fr', $user->getEmail());
        $this->assertEquals('en_US', $user->getLanguage());
    }

    // Update user (someone else)
    public function test_user_update_someone_else(): void
    {
        $token = $this->authenticate('user', 'password');

        $response = static::createClient()->request('PATCH', '/api/users/2', [
            'headers' => ['Authorization' => $token, 'Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'username' => 'toto',
                'email' => 'toto@tutu.fr',
                'language' => 'en_US',
            ],
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    // Update user (admin someone else)
    public function test_user_update_admin_someone_else(): void
    {
        $token = $this->authenticate('admin', 'password');

        $response = static::createClient()->request('PATCH', '/api/users/2', [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'username' => 'toto',
                'email' => 'toto@tutu.fr',
                'language' => 'en_US',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        /** @var User|null $user */
        $user = $this->emi->getRepository(User::class)->find(2);
        $this->assertNotNull($user);

        $this->assertEquals('toto', $user->getUsername());
        $this->assertEquals('toto@tutu.fr', $user->getEmail());
        $this->assertEquals('en_US', $user->getLanguage());
    }

    public function test_user_update_appliance(): void
    {
        $response = static::createClient()->request('PATCH', '/api/users/2', [
            'json' => [
                'username' => 'toto',
                'email' => 'toto@tutu.fr',
                'language' => 'en_US',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_ban_unauthorized(): void
    {
        $response = static::createClient()->request('POST', '/api/users/2/ban', [
            'json' => [],
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_ban_user(): void
    {
        $token = $this->authenticate('user', 'password');

        $response = static::createClient()->request('POST', '/api/users/2/ban', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_ban_appliance(): void
    {
        $response = static::createClient()->request('POST', '/api/users/2/ban', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
            'json' => [],
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_ban_admin(): void
    {
        /** @var User|null $user */
        $user = $this->emi->getRepository(User::class)->find(2);
        $this->assertNull($user->getBannedAt());

        $token = $this->authenticate('admin', 'password');

        $response = static::createClient()->request('POST', '/api/users/2/ban', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        $this->assertJsonContains([
            '@id' => '/api/users/2',
            'id' => 2,
            'username' => 'eventmaker',
            'email' => 'eventmaker@partyhall.dev',
        ]);

        /** @var User|null $user */
        $user = $this->emi->getRepository(User::class)->find(2);
        $this->assertNotNull($user->getBannedAt());
    }

    public function test_unban_unauthorized(): void
    {
        $response = static::createClient()->request('POST', '/api/users/2/unban', [
            'json' => [],
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_unban_user(): void
    {
        $token = $this->authenticate('user', 'password');

        $response = static::createClient()->request('POST', '/api/users/2/unban', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_unban_appliance(): void
    {
        $response = static::createClient()->request('POST', '/api/users/2/unban', [
            'headers' => [
                'X-HARDWARE-ID' => self::APPLIANCE_KEY,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
            'json' => [],
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_unban_admin(): void
    {
        /** @var User|null $user */
        $user = $this->emi->getRepository(User::class)->find(2);
        $this->assertNotNull($user);
        $user->setBannedAt(new \DateTimeImmutable());
        $this->emi->flush();

        $token = $this->authenticate('admin', 'password');

        $response = static::createClient()->request('POST', '/api/users/2/unban', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        $this->assertJsonContains([
            '@id' => '/api/users/2',
            'id' => 2,
            'username' => 'eventmaker',
            'email' => 'eventmaker@partyhall.dev',
        ]);

        /** @var User|null $user */
        $user = $this->emi->getRepository(User::class)->find(2);
        $this->assertNull($user->getBannedAt());
    }
}
