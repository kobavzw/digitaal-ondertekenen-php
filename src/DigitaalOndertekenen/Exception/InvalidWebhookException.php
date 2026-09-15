<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Exception;

use UnexpectedValueException;

/** Thrown when an inbound SigningHub webhook cannot be safely parsed. */
final class InvalidWebhookException extends UnexpectedValueException
{
}
