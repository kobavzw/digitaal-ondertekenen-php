<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Cache;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * The part of a Digitaal Ondertekenen access token that needs to be cached.
 *
 * The ACM token is deliberately not represented here. It is only used while
 * obtaining the Digitaal Ondertekenen token.
 */
final class CachedToken
{
    public function __construct(
        private string $token,
        private ?int $expiresAt,
    ) {
    }

    public static function fromAccessToken(AccessTokenInterface $token): self
    {
        return new self($token->getToken(), $token->getExpires());
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }

    /**
     * A small safety margin prevents a token expiring while a request is in
     * flight. Tokens without an expiry are not safe to cache.
     */
    public function isUsable(int $now, int $leeway = 60): bool
    {
        return $this->token !== ''
            && $this->expiresAt !== null
            && $this->expiresAt > $now + $leeway;
    }

    public function toAccessToken(): AccessTokenInterface
    {
        $options = ['access_token' => $this->token];

        if ($this->expiresAt !== null) {
            $options['expires'] = $this->expiresAt;
        }

        return new AccessToken($options);
    }
}
