<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Api;

final readonly class AddSignatureFieldResponse
{
    public function __construct(public string $field_name)
    {
    }
}
