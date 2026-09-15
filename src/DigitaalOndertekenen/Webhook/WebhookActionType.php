<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Webhook;

/** Action types observed in SigningHub document-processing webhooks. */
enum WebhookActionType: string
{
    case SHARED = 'SHARED';
    case SIGNED = 'SIGNED';
    case REVIEWED = 'REVIEWED';
    case DECLINED = 'DECLINED';
    case EDITED = 'EDITED';
    case CARBON_COPIED = 'CARBON_COPIED';
    case RECALLED = 'RECALLED';
    case REMINDED = 'REMINDED';
    case COMPLETED = 'COMPLETED';
    case EVIDENCE_REPORT_GENERATED = 'EVIDENCE_REPORT_GENERATED';
    case DOCUMENT_DELETED = 'DOCUMENT_DELETED';
}
