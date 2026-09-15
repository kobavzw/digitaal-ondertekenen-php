<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Call;

use InvalidArgumentException;
use JsonMapper\JsonMapperInterface;
use Koba\DigitaalOndertekenen\Http\ApiTransport;
use Koba\DigitaalOndertekenen\Http\HttpMethod;
use Psr\Http\Message\ResponseInterface;

/**
 * @extends AbstractCall<string>
 */
final class DownloadDocumentCall extends AbstractCall
{
    public function __construct(
        ApiTransport $transport,
        private int $packageId,
        private int $documentId,
        private ?string $password = null,
        private ?string $otp = null,
        private ?string $folderId = null,
    ) {
        parent::__construct($transport);

        if ($this->packageId < 1 || $this->documentId < 1) {
            throw new InvalidArgumentException('The package and document IDs must be greater than zero.');
        }

        $this->validateOptionalHeader($this->password, 'document password');
        $this->validateOptionalHeader($this->otp, 'one-time password');
        $this->validateOptionalHeader($this->folderId, 'folder ID');
    }

    public function getMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    public function getEndpoint(): string
    {
        return sprintf('/v4/packages/%d/documents/%d', $this->packageId, $this->documentId);
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($this->password !== null) {
            $headers['x-password'] = $this->password;
        }

        if ($this->otp !== null) {
            $headers['x-otp'] = $this->otp;
        }

        if ($this->folderId !== null) {
            $headers['x-folder-id'] = $this->folderId;
        }

        return $headers;
    }

    public function processResponse(
        JsonMapperInterface $mapper,
        ResponseInterface $response,
    ): string {
        return (string) $response->getBody();
    }

    private function validateOptionalHeader(?string $value, string $name): void
    {
        if ($value !== null && ($value === '' || strpbrk($value, "\r\n") !== false)) {
            throw new InvalidArgumentException(sprintf(
                'The %s must be non-empty and must not contain line breaks.',
                $name,
            ));
        }
    }
}
