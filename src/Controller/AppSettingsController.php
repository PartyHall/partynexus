<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
readonly class AppSettingsController
{
    public function __construct(
        #[Autowire(env: 'PUBLIC_URL')]
        private string $nexusPublicUrl,
        #[Autowire(param: 'oauth.enabled')]
        private bool   $oauthEnabled,
        #[Autowire(param: 'oauth.base_url')]
        private string $oauthBaseUrl,
        #[Autowire(param: 'oauth.client_id')]
        private string $oauthClientId,
        #[Autowire(param: 'oauth.button_icon')]
        private string $oauthButtonIcon,
        #[Autowire(param: 'oauth.button_text')]
        private string $oauthButtonText,
        #[Autowire(param: 'oauth.button_colors.main')]
        private string $oauthButtonColorsMain,
        #[Autowire(param: 'oauth.button_colors.hover')]
        private string $oauthButtonColorsHover,
        #[Autowire(param: 'oauth.button_colors.text')]
        private string $oauthButtonColorsText,
        #[Autowire(env: 'SPOTIFY_CLIENT_ID')]
        private string $spotifyClientId = '',
        #[Autowire(env: 'SPOTIFY_CLIENT_SECRET')]
        private string $spotifyClientSecret = '',
    )
    {
    }

    #[Route(path: '/api/settings', name: 'nexus_settings', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $settings = [];

        if ($this->oauthEnabled) {
            $baseUrl = \trim(\rtrim($this->oauthBaseUrl, '/')) . '/auth?';
            $nexusPublicUrl = \trim(\rtrim($this->nexusPublicUrl, '/')) . '/oauth-callback';

            $queryString = \http_build_query([
                'client_id' => \trim($this->oauthClientId),
                'response_type' => 'code',
                'scope' => 'openid profile email',
                'redirect_uri' => $nexusPublicUrl,
            ]);

            $btColor = \trim($this->oauthButtonColorsMain);
            $btHoverColor = \trim($this->oauthButtonColorsHover);
            $btTextColor = \trim($this->oauthButtonColorsText);

            $settings['oauth'] = [
                'loginUrl' => $baseUrl . $queryString,
                'buttonIcon' => \trim($this->oauthButtonIcon),
                'buttonText' => \trim($this->oauthButtonText),
                'buttonCss' => <<<CSS
#oauthButton {
    background-color: {$btColor} !important;
    color: {$btTextColor} !important;
}

#oauthButton:hover {
    background-color: {$btHoverColor} !important;
    color: {$btTextColor} !important;
}
CSS,
            ];
        }

        if (strlen(\trim($this->spotifyClientId)) > 0 && strlen(\trim($this->spotifyClientSecret)) > 0) {
            $settings['spotify_enabled'] = true;

        }

        return new JsonResponse($settings);
    }
}
