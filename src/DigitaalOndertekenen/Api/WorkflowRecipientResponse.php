<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Api;

final readonly class WorkflowRecipientResponse
{
    public function __construct(
        public string $user_email,
        public ?int $signing_order = null,
        public ?bool $guest_user = null,
        public ?string $email_language_code = null,
    ) {
    }
}
