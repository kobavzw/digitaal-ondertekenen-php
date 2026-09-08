<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Api;

final readonly class StartWorkflowResponse
{
    /**
     * @param list<int> $documents
     */
    public function __construct(
        public int $package_id,
        public array $documents,
    ) {
    }
}
