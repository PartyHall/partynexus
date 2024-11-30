<?php

namespace App\Tests\Users;

use App\Entity\User;
use App\Tests\AuthenticatedTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class BanUserTest extends AuthenticatedTestCase
{
    /** @var EntityRepository<User> */
    private EntityRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->emi->getRepository(User::class);
    }

    // Ban a user (user account) => 403
    public function test_ban_by_user(): void
    {
        $token = self::authenticate('user', 'password');

        $resp = static::createClient()->request('POST', '/api/users/2/ban', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);
        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Ban a user (user account) => 403
    public function test_unban_by_user(): void
    {
        $token = self::authenticate('user', 'password');

        $resp = static::createClient()->request('POST', '/api/users/2/unban', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);
        $this->assertEquals(403, $resp->getStatusCode());
    }

    // Test ban an existing user => 201, re-banning them should not change the datetime
    public function test_ban_admin_again(): void
    {
        $token = self::authenticate('admin', 'password');

        $resp = static::createClient()->request('POST', '/api/users/3/ban', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);

        $this->assertEquals(201, $resp->getStatusCode());

        /** @var User $user */
        $user = $this->userRepository->find(3);
        $bannedAt = $user->getBannedAt();

        $this->assertNotNull($bannedAt);

        $resp = static::createClient()->request('POST', '/api/users/3/ban', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);

        $this->assertEquals(201, $resp->getStatusCode());

        /** @var User $user */
        $user = $this->userRepository->find(3);
        $this->assertEquals($bannedAt, $user->getBannedAt());
    }

    // Test unban a banned user => 201
    /**
     * For some reason if the assertNull/assertNotNull are not exactly in these places
     * the test fail. I should debug at some point to understand
     */
    public function test_unban_admin(): void
    {
        /** @var User $user */
        $user = $this->userRepository->find(3);
        $user->setBannedAt(new \DateTimeImmutable());
        $this->emi->persist($user);
        $this->emi->flush();

        /** @var User $user */
        $user = $this->userRepository->find(3);
        $this->assertNotNull($user->getBannedAt());

        $token = self::authenticate('admin', 'password');

        $resp = static::createClient()->request('POST', '/api/users/3/unban', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);

        $this->assertEquals(201, $resp->getStatusCode());

        /** @var User $user */
        $user = $this->userRepository->find(3);
        $this->assertNull($user->getBannedAt());
    }

    // Test unban an unbanned user => 201, no changes
    public function test_unban_admin_not_banned(): void
    {
        $token = self::authenticate('admin', 'password');

        $resp = static::createClient()->request('POST', '/api/users/3/unban', [
            'headers' => ['Authorization' => $token],
            'json' => [],
        ]);

        $this->assertEquals(201, $resp->getStatusCode());

        /** @var User $user */
        $user = $this->userRepository->find(3);
        $this->assertNull($user->getBannedAt());
    }

    // A banned user should not be able to login (username+password)
    public function test_banned_login_usernamepassword(): void
    {
        /** @var User $user */
        $user = $this->userRepository->find(3);
        $user->setBannedAt(new \DateTimeImmutable());
        $this->emi->persist($user);
        $this->emi->flush();

        $resp = static::createClient()->request('POST', '/api/login', [
            'json' => [
                'username' => 'user',
                'password' => 'password',
            ]
        ]);

        $this->assertEquals(401, $resp->getStatusCode());
    }

    // @TODO: A banned user should not be able to login (magic link)
    // IDK how to fake mails I need to learn (regarding workers too!)
}
