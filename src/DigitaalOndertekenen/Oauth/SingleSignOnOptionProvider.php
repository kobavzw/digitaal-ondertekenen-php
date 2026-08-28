<?php

namespace Koba\DigitaalOndertekenen\Oauth;

use League\OAuth2\Client\OptionProvider\OptionProviderInterface;

class SingleSignOnOptionProvider implements OptionProviderInterface
{
    /**
     * @param array<string, mixed> $params
     * @return array{headers: array<string, string>, body: string}
     */
    public function getAccessTokenOptions($method, array $params)
    {
        return [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'token' => $params['token'],
                'method' => 'OAUTH2',
                'profile_name' => $params['profile_name'],
                'client_id' => $params['client_id'],
                'client_secret' => $params['client_secret'],
            ], JSON_THROW_ON_ERROR),
        ];
    }
}
