<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Api;

final readonly class AddPackageResponse
{
    public function __construct(
        public int $package_id,
        public ?string $workflow_mode = null,
        public ?string $workflow_type = null,
    ) {
    }
}
