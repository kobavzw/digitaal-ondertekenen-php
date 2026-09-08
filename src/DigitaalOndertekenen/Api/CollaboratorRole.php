<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Api;

/**
 * The action assigned to a workflow recipient.
 *
 * XML document workflows only support signer, reviewer, and carbon-copy roles.
 */
enum CollaboratorRole: string
{
    /** Signs the signature fields assigned to them. */
    case SIGNER = 'SIGNER';

    /** Reviews and approves or declines the document without signing it. */
    case REVIEWER = 'REVIEWER';

    /** Edits the document as part of the workflow. */
    case EDITOR = 'EDITOR';

    /** Receives a copy without being assigned a processing action. */
    case CARBON_COPY = 'CARBON_COPY';

    /** Hosts an in-person signing session for another person. */
    case INPERSON_HOST = 'INPERSON_HOST';
}
