<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class AuthenticatedTestCase extends ApiTestCase
{
    protected const string APPLIANCE_KEY = 'b094786e-5158-4ceb-861b-28cb45b2a2c3';
    protected const string APPLIANCE_SECRET = 'my-api-token';
    protected const string APPLIANCE_NOT_OWNER_KEY = 'c105897f-6169-5dfc-962c-39dc56c3b3d4';
    protected const string APPLIANCE_NOT_OWNER_SECRET = 'my-api-token';

    protected function authenticate(string $username, string $password): string
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

    protected function getUnauthenticated(string $url, int $statusCode): void
    {
        $response = static::createClient()->request('GET', $url);

        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    protected function getUser(string $url, int $statusCode, string $username): void
    {
        $token = $this->authenticate($username, 'password');

        $response = static::createClient()->request('GET', $url, [
            'headers' => [
                'Authorization' => $token,
            ],
        ]);

        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    protected function getAdmin(string $url, int $statusCode): void
    {
        $this->getUser($url, $statusCode, 'admin');
    }

    protected function getAppliance(string $url, int $statusCode, string $applianceKey = self::APPLIANCE_KEY): void
    {
        $response = static::createClient()->request('GET', $url, [
            'headers' => [
                'X-HARDWARE-ID' => $applianceKey,
                'X-API-TOKEN' => self::APPLIANCE_SECRET,
            ],
        ]);

        $this->assertEquals($statusCode, $response->getStatusCode());
    }
}
