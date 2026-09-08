<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Api;

/**
 * Signature classes supported by the SigningHub API.
 *
 * Availability depends on the service plan, user role, and configured signing
 * providers. Selecting a value does not by itself guarantee its legal effect.
 */
enum SignatureLevelOfAssurance: string
{
    /** Use SigningHub's Simple Electronic Signature (SES) flow. */
    case ELECTRONIC_SIGNATURE = 'ELECTRONIC_SIGNATURE';

    /** Require an Advanced Electronic Signature (AES). */
    case ADVANCED_ELECTRONIC_SIGNATURE = 'ADVANCED_ELECTRONIC_SIGNATURE';

    /** Require SigningHub's High Trust Advanced (AATL) signature. */
    case HIGH_TRUST_ADVANCED = 'HIGH_TRUST_ADVANCED';

    /** Require a Qualified Electronic Signature (QES). */
    case QUALIFIED_ELECTRONIC_SIGNATURE = 'QUALIFIED_ELECTRONIC_SIGNATURE';
}
