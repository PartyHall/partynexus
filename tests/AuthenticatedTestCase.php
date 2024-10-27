<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class AuthenticatedTestCase extends ApiTestCase
{
    protected const string APPLIANCE_KEY = 'b094786e-5158-4ceb-861b-28cb45b2a2c3';
    protected const string APPLIANCE_SECRET = 'my-api-token';
    protected const string APPLIANCE_NOT_OWNER_KEY = 'c105897f-6169-5dfc-962c-39dc56c3b3d4';
    protected const string APPLIANCE_NOT_OWNER_SECRET = 'my-api-token2';

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
}
