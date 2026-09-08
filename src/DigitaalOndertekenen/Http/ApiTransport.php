<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Http;

use InvalidArgumentException;
use JsonMapper\JsonMapperInterface;
use Koba\DigitaalOndertekenen\Call\AbstractCall;
use Koba\DigitaalOndertekenen\Environment;
use Koba\DigitaalOndertekenen\Exception\ApiException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Builds and sends API requests for calls and maps successful responses.
 *
 * Authentication remains a concern of the PSR-18 client supplied to this
 * transport, which allows the transport to stay independent of OAuth.
 */
final class ApiTransport
{
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private Environment $environment,
        private JsonMapperInterface $jsonMapper,
    ) {
    }

    /**
     * @template TResponse
     * @param AbstractCall<TResponse> $call
     * @return TResponse
     */
    public function execute(AbstractCall $call): mixed
    {
        $request = $this->createRequest($call->getMethod()->value, $call->getEndpoint());

        foreach ($call->getHeaders() as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $body = $call->getBody();

        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);

            if (!$request->hasHeader('Content-Type')) {
                $request = $request->withHeader('Content-Type', 'application/json');
            }
        }

        if ($body !== null) {
            $request = $request->withBody(
                $body instanceof StreamInterface
                    ? $body
                    : $this->streamFactory->createStream($body),
            );
        }

        $response = $this->sendRequest($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw ApiException::fromResponse($response);
        }

        return $call->processResponse($this->jsonMapper, $response);
    }

    /**
     * Sends a request through the configured, potentially authenticated,
     * PSR-18 client.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->httpClient->sendRequest($request);
    }

    private function createRequest(string $method, string $endpoint): RequestInterface
    {
        if ($endpoint === '' || str_starts_with($endpoint, '//') || filter_var($endpoint, FILTER_VALIDATE_URL) !== false) {
            throw new InvalidArgumentException('A call endpoint must be a relative URI path.');
        }

        return $this->requestFactory->createRequest(
            $method,
            rtrim($this->environment->getApiBaseUri(), '/') . '/' . ltrim($endpoint, '/'),
        );
    }
}
