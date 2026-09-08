<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Api;

final readonly class UploadDocumentResponse
{
    public function __construct(
        public int $document_id,
        public ?string $document_name = null,
        public ?string $document_type = null,
        public ?int $document_pages = null,
        public ?int $document_size = null,
    ) {
    }
}
