<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Call;

use JsonMapper\JsonMapperInterface;
use Koba\DigitaalOndertekenen\Api\AddPackageResponse;
use Koba\DigitaalOndertekenen\Api\WorkflowMode;
use Koba\DigitaalOndertekenen\Http\ApiTransport;
use Koba\DigitaalOndertekenen\Http\HttpMethod;
use Psr\Http\Message\ResponseInterface;

/**
 * @extends AbstractCall<AddPackageResponse>
 */
final class CreatePackageCall extends AbstractCall
{
    public function __construct(
        ApiTransport $transport,
        private WorkflowMode $workflowMode,
        private ?string $packageName = null,
        private ?string $folderName = null,
    ) {
        parent::__construct($transport);
    }

    public function getMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    public function getEndpoint(): string
    {
        return '/v4/packages';
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

    /**
     * @return array<string, string>
     */
    public function getBody(): array
    {
        $payload = ['workflow_mode' => $this->workflowMode->value];

        if ($this->packageName !== null) {
            $payload['package_name'] = $this->packageName;
        }

        if ($this->folderName !== null) {
            $payload['folder_name'] = $this->folderName;
        }

        return $payload;
    }

    public function processResponse(
        JsonMapperInterface $mapper,
        ResponseInterface $response,
    ): AddPackageResponse
    {
        return $mapper->mapToClassFromString(
            (string) $response->getBody(),
            AddPackageResponse::class,
        );
    }
}
