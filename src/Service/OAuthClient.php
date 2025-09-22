<?php

namespace App\Service;

use App\Model\OAuthUserInfos;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OAuthClient
{
    private string $baseUrl;
    private string $oauthUrl;

    public function __construct(
        #[Autowire(env: 'PUBLIC_URL')]
        string $baseUrl,
        #[Autowire(param: 'oauth.base_url')]
        string $oauthUrl,
        #[Autowire(param: 'oauth.client_id')]
        private readonly string $clientId,
        #[Autowire(param: 'oauth.client_secret')]
        private readonly string $clientSecret,
        private readonly HttpClientInterface $http,
    ) {
        $this->baseUrl = \rtrim($baseUrl, '/');
        $this->oauthUrl = \rtrim($oauthUrl, '/');
    }

    public function exchangeToken(string $code): ?string
    {
        // Exchange the code from the frontend for a oAuth access token
        $resp = $this->http->request('POST', $this->oauthUrl.'/token', [
            'body' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $this->baseUrl.'/oauth-callback',
            ],
        ]);

        $content = \json_decode($resp->getContent(), true);

        return $content['access_token'];
    }

    public function getUserInfo(string $token): OAuthUserInfos
    {
        $parser = new Parser(new JoseEncoder());

        /** @var UnencryptedToken $jwt */
        $jwt = $parser->parse($token);

        $userInfos = new OAuthUserInfos();
        $userInfos->oauthUserId = $jwt->claims()->get('sub'); // scope: openid
        $userInfos->firstName = $jwt->claims()->get('given_name'); // scope: profile
        $userInfos->lastName = $jwt->claims()->get('family_name'); // scope: profile
        $userInfos->email = $jwt->claims()->get('email'); // scope: email
        $userInfos->locale = $jwt->claims()->get('locale'); // scope: email
        $userInfos->username = $jwt->claims()->get('preferred_username'); // scope: idk

        if ($jwt->claims()->has('resource_access')) {
            $userInfos->roles = $this->parseRolesKeycloak($jwt->claims()->get('resource_access'));
        }

        return $userInfos;
    }

    public function exchangeTokenAndGetUserInfos(string $code): OAuthUserInfos
    {
        return $this->getUserInfo($this->exchangeToken($code));
    }

    // region Roles parsing
    // Each OAuth provider has its own way to provide roles/authorities/groups
    // I implemented Keycloak as that's what I use, but feel free to do a
    // pull request for your provider!

    /**
     * @param array<mixed> $resourceAccess
     *
     * @return array<string>
     */
    private function parseRolesKeycloak(array $resourceAccess): array
    {
        /*
         * The app in keycloak must be named "partyhall"
         * The roles assigned to the users must be the same as in Symfony:
         * - ROLE_USER
         * - ROLE_EVENT_MAKER
         * - ROLE_ADMIN
         */
        if (
            !\array_key_exists('partyhall', $resourceAccess)
            || !\array_key_exists('roles', $resourceAccess['partyhall'])
        ) {
            return [];
        }

        return $resourceAccess['partyhall']['roles'];
    }
    // endregion
}
