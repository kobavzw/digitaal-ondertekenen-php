<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Api;

/** Determines how action-status parameters are appended to the callback URL. */
enum IntegrationResponseType: string
{
    /** Append the callback response parameters in encoded form. */
    case ENCODED = 'ENCODED';

    /** Append the callback response as separate, human-readable query parameters. */
    case PLAIN = 'PLAIN';
}
