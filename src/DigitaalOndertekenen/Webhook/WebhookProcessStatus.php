<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Webhook;

/** Processing status of a workflow participant. */
enum WebhookProcessStatus: string
{
    case UN_PROCESSED = 'UN_PROCESSED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case SIGNED = 'SIGNED';
    case REVIEWED = 'REVIEWED';
    case DECLINED = 'DECLINED';
    case EDITED = 'EDITED';
    case INVALID = 'INVALID';
}
