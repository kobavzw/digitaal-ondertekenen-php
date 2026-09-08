<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class ApiException extends RuntimeException
{
    private function __construct(
        string $message,
        private int $statusCode,
        private string $responseBody,
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function fromResponse(ResponseInterface $response): self
    {
        $responseBody = (string) $response->getBody();
        $message = sprintf('Digitaal Ondertekenen API request failed with HTTP %d.', $response->getStatusCode());

        $decodedBody = json_decode($responseBody, true);

        if (is_array($decodedBody)) {
            $providerMessage = $decodedBody['message'] ?? $decodedBody['Message'] ?? $decodedBody['error'] ?? null;

            if (is_string($providerMessage) && $providerMessage !== '') {
                $message .= ' ' . $providerMessage;
            }
        }

        return new self($message, $response->getStatusCode(), $responseBody);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
