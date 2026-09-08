<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Call;

use InvalidArgumentException;
use JsonMapper\JsonMapperInterface;
use Koba\DigitaalOndertekenen\Api\AddSignatureFieldResponse;
use Koba\DigitaalOndertekenen\Api\SignatureLevelOfAssurance;
use Koba\DigitaalOndertekenen\Http\ApiTransport;
use Koba\DigitaalOndertekenen\Http\HttpMethod;
use Psr\Http\Message\ResponseInterface;

/**
 * @extends AbstractCall<AddSignatureFieldResponse>
 */
final class AddSignatureFieldCall extends AbstractCall
{
    /**
     * @param array{x: float, y: float, width: float, height: float} $dimensions
     */
    public function __construct(
        ApiTransport $transport,
        private int $packageId,
        private int $documentId,
        private int $recipientOrder,
        private int $pageNumber,
        private array $dimensions,
        private SignatureLevelOfAssurance $levelOfAssurance,
        private ?string $fieldName = null,
    ) {
        parent::__construct($transport);

        $this->fieldName = $this->fieldName === '' ? null : $this->fieldName;

        if ($this->packageId < 1 || $this->documentId < 1) {
            throw new InvalidArgumentException('The package and document IDs must be greater than zero.');
        }

        if ($this->recipientOrder < 1) {
            throw new InvalidArgumentException('The workflow recipient order must be greater than zero.');
        }

        if ($this->pageNumber < 1) {
            throw new InvalidArgumentException('The signature field page number must be greater than zero.');
        }

        if ($this->dimensions['x'] < 0 || $this->dimensions['y'] < 0) {
            throw new InvalidArgumentException('Signature field coordinates cannot be negative.');
        }

        if ($this->dimensions['width'] <= 0 || $this->dimensions['height'] <= 0) {
            throw new InvalidArgumentException('Signature field dimensions must be greater than zero.');
        }
    }

    public function getMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    public function getEndpoint(): string
    {
        return sprintf('/v4/packages/%d/documents/%d/fields/signature', $this->packageId, $this->documentId);
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
     * @return array<string, array<float|string>|int|string>
     */
    public function getBody(): array
    {
        $payload = [
            'order' => $this->recipientOrder,
            'page_no' => $this->pageNumber,
            'level_of_assurance' => [$this->levelOfAssurance->value],
            'dimensions' => $this->dimensions,
            'display' => 'VISIBLE',
        ];

        if ($this->fieldName !== null) {
            $payload['field_name'] = $this->fieldName;
        }

        return $payload;
    }

    public function processResponse(JsonMapperInterface $mapper, ResponseInterface $response): AddSignatureFieldResponse
    {
        return $mapper->mapToClassFromString((string) $response->getBody(), AddSignatureFieldResponse::class);
    }
}
