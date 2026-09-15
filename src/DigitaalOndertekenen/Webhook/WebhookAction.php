<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Webhook;

/** The action that caused a document-processing webhook to be delivered. */
final readonly class WebhookAction
{
    public ?WebhookActionType $type;

    public function __construct(
        public string $raw_type,
        public string $date_time,
        public string $performed_by,
    ) {
        $this->type = WebhookActionType::tryFrom($raw_type);
    }
}
