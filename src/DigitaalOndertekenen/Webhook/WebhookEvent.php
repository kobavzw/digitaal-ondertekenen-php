<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Webhook;

/** A validated SigningHub document-processing webhook. */
final readonly class WebhookEvent
{
    /**
     * @param list<WebhookDocument> $documents
     * @param list<WebhookWorkflowUser> $workflowUsers
     * @internal Webhook events must be created through WebhookParser.
     */
    public function __construct(
        private string $rawXml,
        private int $packageId,
        private string $packageName,
        private string $packageOwner,
        private string $packageStatus,
        private WebhookAction $action,
        private ?string $nextSignerEmail,
        private string $workflowType,
        private string $workflowStatus,
        private array $documents,
        private array $workflowUsers,
    ) {
    }

    public function getRawXml(): string
    {
        return $this->rawXml;
    }

    /**
     * Returns a stable fingerprint of the received bytes.
     *
     * This is useful as a fallback idempotency key when the report does not
     * contain a vendor event ID. It is not proof of the webhook's authenticity.
     */
    public function getFingerprint(): string
    {
        return hash('sha256', $this->rawXml);
    }

    public function getPackageId(): int
    {
        return $this->packageId;
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getPackageOwner(): string
    {
        return $this->packageOwner;
    }

    public function getPackageStatus(): string
    {
        return $this->packageStatus;
    }

    public function getAction(): WebhookAction
    {
        return $this->action;
    }

    public function getNextSignerEmail(): ?string
    {
        return $this->nextSignerEmail;
    }

    public function getWorkflowType(): string
    {
        return $this->workflowType;
    }

    public function getWorkflowStatus(): string
    {
        return $this->workflowStatus;
    }

    /**
     * @return list<WebhookDocument>
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @return list<WebhookWorkflowUser>
     */
    public function getWorkflowUsers(): array
    {
        return $this->workflowUsers;
    }
}
