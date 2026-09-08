<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Cache;

/**
 * Keeps a token in memory for the lifetime of the cache instance.
 *
 * This is suitable as the default for a single client instance. Applications
 * that need token sharing across requests or processes should provide a
 * persistent TokenCacheInterface implementation instead.
 */
final class InMemoryTokenCache implements TokenCacheInterface
{
    private ?CachedToken $token = null;

    public function get(): ?CachedToken
    {
        return $this->token;
    }

    public function save(CachedToken $token): void
    {
        $this->token = $token;
    }

    public function delete(): void
    {
        $this->token = null;
    }
}
