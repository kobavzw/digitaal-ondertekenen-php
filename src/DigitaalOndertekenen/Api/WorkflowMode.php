<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Api;

enum WorkflowMode: string
{
    /** The package owner and other workflow recipients participate. */
    case ME_AND_OTHERS = 'ME_AND_OTHERS';

    /** Only recipients other than the package owner participate. */
    case ONLY_OTHERS = 'ONLY_OTHERS';

    /** Only the package owner participates. */
    case ONLY_ME = 'ONLY_ME';
}
