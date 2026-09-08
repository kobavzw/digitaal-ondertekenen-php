<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Call;

use JsonMapper\JsonMapperInterface;
use Koba\DigitaalOndertekenen\Http\ApiTransport;
use Koba\DigitaalOndertekenen\Http\HttpMethod;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @template TResponse
 */
abstract class AbstractCall
{
    public function __construct(
        protected ApiTransport $transport,
    ) {
    }

    /**
     * Executes this call and maps the response to its result.
     *
     * @return TResponse
     */
    final public function send(): mixed
    {
        return $this->transport->execute($this);
    }

    abstract public function getMethod(): HttpMethod;

    /**
     * @return non-empty-string
     */
    abstract public function getEndpoint(): string;

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }

    /**
     * An array body is encoded as JSON by the transport.
     *
     * @return array<mixed>|string|StreamInterface|null
     */
    public function getBody(): array|string|StreamInterface|null
    {
        return null;
    }

    /**
     * @return TResponse
     */
    abstract public function processResponse(
        JsonMapperInterface $mapper,
        ResponseInterface $response,
    ): mixed;
}
