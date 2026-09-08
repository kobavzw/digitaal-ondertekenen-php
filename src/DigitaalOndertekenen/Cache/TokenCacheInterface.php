<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Cache;

/**
 * Storage abstraction for the second-stage Digitaal Ondertekenen token.
 *
 * Implementations can use any storage mechanism, such as an in-memory array,
 * Symfony Cache, Redis, or a framework-specific cache pool. The manager is
 * responsible for deciding whether the returned token is still usable.
 */
interface TokenCacheInterface
{
    public function get(): ?CachedToken;

    public function save(CachedToken $token): void;

    public function delete(): void;
}
