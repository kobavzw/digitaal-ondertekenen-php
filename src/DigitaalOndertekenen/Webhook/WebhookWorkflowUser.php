<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Webhook;

/** A workflow participant included in a document-processing webhook. */
final readonly class WebhookWorkflowUser
{
    public ?WebhookProcessStatus $process_status;

    public function __construct(
        public int $order,
        public string $user_name,
        public string $user_email,
        public string $raw_process_status,
        public ?string $processed_on,
        public ?string $processed_as,
        public ?int $signing_order,
    ) {
        $this->process_status = WebhookProcessStatus::tryFrom($raw_process_status);
    }
}
