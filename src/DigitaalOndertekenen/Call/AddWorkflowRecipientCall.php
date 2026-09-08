<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Call;

use InvalidArgumentException;
use JsonMapper\JsonMapperInterface;
use Koba\DigitaalOndertekenen\Api\CollaboratorRole;
use Koba\DigitaalOndertekenen\Api\WorkflowRecipientResponse;
use Koba\DigitaalOndertekenen\Http\ApiTransport;
use Koba\DigitaalOndertekenen\Http\HttpMethod;
use Psr\Http\Message\ResponseInterface;

/**
 * @extends AbstractCall<WorkflowRecipientResponse>
 */
final class AddWorkflowRecipientCall extends AbstractCall
{
    public function __construct(
        ApiTransport $transport,
        private int $packageId,
        private string $email,
        private string $name,
        private CollaboratorRole $role = CollaboratorRole::SIGNER,
        private bool $emailNotification = true,
        private ?int $signingOrder = null,
    ) {
        parent::__construct($transport);

        if ($this->packageId < 1) {
            throw new InvalidArgumentException('The package ID must be greater than zero.');
        }

        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('The workflow recipient email address is invalid.');
        }

        if ($this->name === '') {
            throw new InvalidArgumentException('The workflow recipient name cannot be empty.');
        }

        if ($this->signingOrder !== null && $this->signingOrder < 1) {
            throw new InvalidArgumentException('The signing order must be greater than zero.');
        }
    }

    public function getMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    public function getEndpoint(): string
    {
        return sprintf('/v4/packages/%d/workflow/users', $this->packageId);
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
     * @return list<array<string, bool|int|string>>
     */
    public function getBody(): array
    {
        $recipient = [
            'user_email' => $this->email,
            'user_name' => $this->name,
            'email_notification' => $this->emailNotification,
            'role' => $this->role->value,
            'delivery_method' => 'EMAIL',
        ];

        if ($this->signingOrder !== null) {
            $recipient['signing_order'] = $this->signingOrder;
        }

        return [$recipient];
    }

    public function processResponse(
        JsonMapperInterface $mapper,
        ResponseInterface $response,
    ): WorkflowRecipientResponse
    {
        return $mapper->mapToClassArrayFromString(
            (string) $response->getBody(),
            WorkflowRecipientResponse::class,
        )[0];
    }
}
