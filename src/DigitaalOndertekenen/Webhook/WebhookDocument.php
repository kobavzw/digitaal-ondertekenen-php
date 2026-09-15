<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Webhook;

/** A document included in a document-processing webhook. */
final readonly class WebhookDocument
{
    public function __construct(
        public int $document_id,
        public string $document_name,
        public string $document_type,
        public int $document_order,
        public ?string $document_source,
        public ?int $document_pages,
    ) {
    }
}
