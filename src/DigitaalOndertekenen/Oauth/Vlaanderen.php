<?php

namespace Koba\DigitaalOndertekenen\Oauth;

use Koba\DigitaalOndertekenen\Exception\OnlyClientCredentialsException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Vlaanderen extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const ACCESS_TOKEN_URL = 'https://authenticatie.vlaanderen.be/op/v1/token';

    /**
     * @var string
     */
    protected $accessTokenUrl = self::ACCESS_TOKEN_URL;

    public function getBaseAuthorizationUrl(): string
    {
        throw new OnlyClientCredentialsException();
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->accessTokenUrl;
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        throw new OnlyClientCredentialsException();
    }

    /**
     * @return list<string>
     */
    protected function getDefaultScopes(): array
    {
        return [];
    }

    protected function getScopeSeparator(): string
    {
        // ACM expects multiple scopes separated by spaces.
        return ' ';
    }

    /**
     * @param array<string, mixed>|string $data
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $hasOAuthError = is_array($data) && isset($data['error']);
        $hasHttpError = $response->getStatusCode() >= 400;

        if (!$hasOAuthError && !$hasHttpError) {
            return;
        }

        if (is_array($data)) {
            $message = $data['error_description']
                ?? $data['error']
                ?? var_export($data, true);
        } else {
            $message = $data;
        }

        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        throw new IdentityProviderException(
            $message,
            $response->getStatusCode(),
            $data
        );
    }

    /**
     * @param array<string, mixed> $response
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        throw new OnlyClientCredentialsException();
    }
}
