<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Auth;

use InvalidArgumentException;
use Koba\DigitaalOndertekenen\Cache\CachedToken;
use Koba\DigitaalOndertekenen\Cache\TokenCacheInterface;
use Koba\DigitaalOndertekenen\Oauth\DigitaalOndertekenen;
use Koba\DigitaalOndertekenen\Oauth\Vlaanderen;
use League\OAuth2\Client\Grant\ClientCredentials;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Obtains and caches a Digitaal Ondertekenen access token.
 *
 * The ACM token is an implementation detail of the two-stage exchange and is
 * never written to the cache.
 *
 * @internal
 */
final class TokenManager
{
    private const DEFAULT_LEEWAY = 60;

    public function __construct(
        private Vlaanderen $vlaanderenProvider,
        private DigitaalOndertekenen $digitaalOndertekenenProvider,
        private string $profileName,
        private TokenCacheInterface $cache,
        private int $leeway = self::DEFAULT_LEEWAY,
    ) {
        if ($this->leeway < 0) {
            throw new InvalidArgumentException('The token expiry leeway cannot be negative.');
        }
    }

    public function getAccessToken(): AccessTokenInterface
    {
        $cachedToken = $this->cache->get();

        if ($cachedToken !== null && $cachedToken->isUsable(time(), $this->leeway)) {
            return $cachedToken->toAccessToken();
        }

        if ($cachedToken !== null) {
            $this->cache->delete();
        }

        $acmToken = $this->vlaanderenProvider->getAccessToken(new ClientCredentials());
        $token = $this->digitaalOndertekenenProvider->getAccessTokenFromOAuthToken(
            $acmToken,
            $this->profileName,
        );

        $tokenToCache = CachedToken::fromAccessToken($token);

        // A token without an expiry cannot be safely validated later.
        if ($tokenToCache->getExpiresAt() !== null) {
            $this->cache->save($tokenToCache);
        }

        return $token;
    }

    /**
     * Removes the cached token without retrying the failed request. This lets
     * callers explicitly invalidate a token while avoiding unsafe automatic
     * retries for non-idempotent API operations.
     */
    public function clearCache(): void
    {
        $this->cache->delete();
    }
}
