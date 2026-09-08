<?php

namespace Koba\DigitaalOndertekenen\Oauth;

use Koba\DigitaalOndertekenen\Environment;
use Koba\DigitaalOndertekenen\Exception\OnlyClientCredentialsException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class DigitaalOndertekenen extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected Environment $environment = Environment::PRODUCTION;

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $collaborators['optionProvider'] ??= new SingleSignOnOptionProvider();
        parent::__construct($options, $collaborators);
    }

    /**
     * Exchange an ACM OAuth token for a Digitaal Ondertekenen token through
     * League's normal access-token pipeline.
     */
    public function getAccessTokenFromOAuthToken(
        AccessTokenInterface $oauthToken,
        string $profileName,
    ): AccessTokenInterface {
        return $this->getAccessToken(new SingleSignOnGrant(), [
            'token' => $oauthToken->getToken(),
            'profile_name' => $profileName,
        ]);
    }

    public function getBaseAuthorizationUrl(): string
    {
        throw new OnlyClientCredentialsException();
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->environment->getDigitaalOndertekenenAccessTokenUrl();
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

    /**
     * @param array<string, mixed>|string $data
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() < 400) {
            return;
        }

        $message = is_array($data) && array_key_exists('Message', $data) && is_string($data['Message'])
            ? $data['Message']
            : 'Unknown provider error';

        throw new IdentityProviderException($message, $response->getStatusCode(), $data);
    }

    /**
     * @param array<string, mixed> $response
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        throw new OnlyClientCredentialsException();
    }
}
