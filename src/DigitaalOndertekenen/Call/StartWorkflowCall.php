<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Call;

use InvalidArgumentException;
use JsonMapper\JsonMapperInterface;
use Koba\DigitaalOndertekenen\Api\StartWorkflowResponse;
use Koba\DigitaalOndertekenen\Http\ApiTransport;
use Koba\DigitaalOndertekenen\Http\HttpMethod;
use Psr\Http\Message\ResponseInterface;

/**
 * @extends AbstractCall<StartWorkflowResponse>
 */
final class StartWorkflowCall extends AbstractCall
{
    public function __construct(ApiTransport $transport, private int $packageId)
    {
        parent::__construct($transport);

        if ($this->packageId < 1) {
            throw new InvalidArgumentException('The package ID must be greater than zero.');
        }
    }

    public function getMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    public function getEndpoint(): string
    {
        return sprintf('/v4/packages/%d/workflow', $this->packageId);
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function processResponse(
        JsonMapperInterface $mapper,
        ResponseInterface $response,
    ): StartWorkflowResponse
    {
        return $mapper->mapToClassArrayFromString(
            (string) $response->getBody(),
            StartWorkflowResponse::class,
        )[0];
    }
}
