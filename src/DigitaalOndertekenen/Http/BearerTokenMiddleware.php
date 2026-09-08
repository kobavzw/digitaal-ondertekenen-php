<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Http;

use Koba\DigitaalOndertekenen\Auth\TokenManager;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-18 client decorator that adds the current Digitaal Ondertekenen token.
 *
 * This class intentionally does not know how tokens are acquired or cached.
 */
final class BearerTokenMiddleware implements ClientInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private TokenManager $tokenManager,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $token = $this->tokenManager->getAccessToken();
        $authenticatedRequest = $request->withHeader(
            'Authorization',
            'Bearer ' . $token->getToken(),
        );

        $response = $this->httpClient->sendRequest($authenticatedRequest);

        // Do not retry automatically: signing requests may not be idempotent.
        if ($response->getStatusCode() === 401) {
            $this->tokenManager->clearCache();
        }

        return $response;
    }
}
