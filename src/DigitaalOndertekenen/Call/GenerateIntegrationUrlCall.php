<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Call;

use InvalidArgumentException;
use JsonMapper\JsonMapperInterface;
use Koba\DigitaalOndertekenen\Api\IntegrationResponseType;
use Koba\DigitaalOndertekenen\Http\ApiTransport;
use Koba\DigitaalOndertekenen\Http\HttpMethod;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

/**
 * @extends AbstractCall<string>
 */
final class GenerateIntegrationUrlCall extends AbstractCall
{
    public function __construct(
        ApiTransport $transport,
        private int $packageId,
        private int $documentId,
        private string $language = 'nl-NL',
        private ?string $userEmail = null,
        private ?string $callbackUrl = null,
        private ?IntegrationResponseType $responseType = null,
        private ?bool $collapsePanels = null,
        private ?bool $lockPanels = null,
        private ?bool $redirectCallbackUrl = null,
    ) {
        parent::__construct($transport);

        if ($this->packageId < 1 || $this->documentId < 1) {
            throw new InvalidArgumentException('The package and document IDs must be greater than zero.');
        }

        if (trim($this->language) === '') {
            throw new InvalidArgumentException('The integration URL language cannot be empty.');
        }

        if ($this->userEmail !== null && filter_var($this->userEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('The integration URL recipient email address is invalid.');
        }

        if ($this->callbackUrl !== null && filter_var($this->callbackUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('The integration callback URL is invalid.');
        }

    }

    public function getMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    public function getEndpoint(): string
    {
        return '/v4/links/integration';
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
     * @return array<string, int|string>
     */
    public function getBody(): array
    {
        $payload = [
            'package_id' => $this->packageId,
            'document_id' => $this->documentId,
            'language' => $this->language,
            'section' => 'VIEWER',
        ];

        if ($this->userEmail !== null) {
            $payload['user_email'] = $this->userEmail;
        }

        if ($this->callbackUrl !== null) {
            $payload['callback_url'] = $this->callbackUrl;
        }

        if ($this->responseType !== null) {
            $payload['response_type'] = $this->responseType->value;
        }

        if ($this->collapsePanels !== null) {
            $payload['collapse_panels'] = $this->collapsePanels ? 'true' : 'false';
        }

        if ($this->lockPanels !== null) {
            $payload['lock_panels'] = $this->lockPanels ? 'true' : 'false';
        }

        if ($this->redirectCallbackUrl !== null) {
            $payload['redirect_callback_url'] = $this->redirectCallbackUrl ? 'true' : 'false';
        }

        return $payload;
    }

    public function processResponse(
        JsonMapperInterface $mapper,
        ResponseInterface $response,
    ): string
    {
        $url = json_decode(
            (string) $response->getBody(),
            false,
            512,
            JSON_THROW_ON_ERROR,
        );

        if (!is_string($url)) {
            throw new UnexpectedValueException('Expected the integration URL response to be a JSON string.');
        }

        return $url;
    }
}
