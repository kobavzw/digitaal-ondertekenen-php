<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Call;

use InvalidArgumentException;
use JsonMapper\JsonMapperInterface;
use Koba\DigitaalOndertekenen\Api\UploadDocumentResponse;
use Koba\DigitaalOndertekenen\Http\ApiTransport;
use Koba\DigitaalOndertekenen\Http\HttpMethod;
use Psr\Http\Message\ResponseInterface;

/**
 * @extends AbstractCall<UploadDocumentResponse>
 */
final class UploadDocumentCall extends AbstractCall
{
    public function __construct(
        ApiTransport $transport,
        private int $packageId,
        private string $fileName,
        private string $contents,
        private ?bool $convertDocument = true,
        private ?string $source = null,
    ) {
        parent::__construct($transport);

        if ($this->packageId < 1) {
            throw new InvalidArgumentException('The package ID must be greater than zero.');
        }

        if ($this->fileName === '' || strpbrk($this->fileName, "\r\n") !== false) {
            throw new InvalidArgumentException('The file name must be non-empty and must not contain line breaks.');
        }

        if ($this->contents === '') {
            throw new InvalidArgumentException('The document contents cannot be empty.');
        }
    }

    public function getMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    public function getEndpoint(): string
    {
        return sprintf('/v4/packages/%d/documents', $this->packageId);
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/octet-stream',
            'x-file-name' => $this->fileName,
        ];

        if ($this->convertDocument !== null) {
            $headers['x-convert-document'] = $this->convertDocument ? 'true' : 'false';
        }

        if ($this->source !== null) {
            $headers['x-source'] = $this->source;
        }

        return $headers;
    }

    public function getBody(): string
    {
        return $this->contents;
    }

    public function processResponse(
        JsonMapperInterface $mapper,
        ResponseInterface $response,
    ): UploadDocumentResponse
    {
        return $mapper->mapToClassFromString(
            (string) $response->getBody(),
            UploadDocumentResponse::class,
        );
    }
}
