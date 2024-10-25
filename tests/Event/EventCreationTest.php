<?php

namespace App\Tests\Event;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class EventCreationTest extends ApiTestCase
{
    private const array EVENT_DATA = [
        'name' => 'My event',
        'author' => 'Me',
        'datetime' => '2024-10-25T15:34:54Z',
        'location' => 'At home',
    ];

    private const array EVENT_CREATED_DATA = [
        'name' => 'My event',
        'author' => 'Me',
        'datetime' => '2024-10-25T15:34:54+00:00',
        'location' => 'At home',
        'owner' => '/api/users/1',
        'over' => false,
        'participants' => [],
    ];

    private function authenticate(string $username, string $password): string
    {
        $resp = static::createClient()->request('POST', '/api/login', [
            'json' => [
                'username' => $username,
                'password' => $password,
            ]
        ]);

        $data = json_decode($resp->getContent(), true);

        return 'Bearer ' . $data['token'];
    }

    public function testEventCreationUnauthenticated(): void
    {
        $response = static::createClient()->request('POST', '/api/events', [
            'json' => self::EVENT_DATA,
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testEventCreationUser(): void
    {
        $token = self::authenticate('user', 'password');

        $response = static::createClient()->request('POST', '/api/events', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $token,
            ],
            'json' => self::EVENT_DATA,
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testEventCreationAdmin(): void
    {
        $token = self::authenticate('admin', 'password');

        $response = static::createClient()->request('POST', '/api/events', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $token,
            ],
            'json' => self::EVENT_DATA,
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJsonContains(self::EVENT_CREATED_DATA);
    }
}
