<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Webhook;

use DOMDocument;
use DOMElement;
use Koba\DigitaalOndertekenen\Exception\InvalidWebhookException;
use Koba\DigitaalOndertekenen\Http\HttpMethod;
use Psr\Http\Message\ServerRequestInterface;

/** Parses inbound SigningHub document-processing webhook reports. */
final class WebhookParser
{
    private const ROOT_ELEMENT = 'DocumentProcessingInformation';

    public static function parseRequest(ServerRequestInterface $request): WebhookEvent
    {
        if (strtoupper($request->getMethod()) !== HttpMethod::POST->value) {
            throw new InvalidWebhookException('A SigningHub webhook must use the POST method.');
        }

        return self::parseXml((string) $request->getBody());
    }

    public static function parseXml(string $xml): WebhookEvent
    {
        $document = self::loadDocument($xml);
        $rootElement = $document->documentElement;

        if (!$rootElement instanceof DOMElement) {
            throw new InvalidWebhookException('The webhook body does not contain a root element.');
        }

        if ($rootElement->localName !== self::ROOT_ELEMENT) {
            throw new InvalidWebhookException(sprintf(
                'Expected a %s webhook, received "%s".',
                self::ROOT_ELEMENT,
                $rootElement->localName,
            ));
        }

        $root = WebhookXmlElement::root($rootElement);
        $workflow = $root->requiredElement('workflow');

        return new WebhookEvent(
            rawXml: $xml,
            packageId: $root->requiredInteger('package_id'),
            packageName: $root->requiredString('package_name'),
            packageOwner: $root->requiredString('package_owner'),
            packageStatus: $root->requiredString('package_status'),
            action: self::parseAction($root->requiredElement('current_action')),
            nextSignerEmail: $root->optionalString('next_signer_email'),
            workflowType: $workflow->requiredString('workflow_type'),
            workflowStatus: $workflow->requiredString('workflow_status'),
            documents: self::parseDocuments($root->optionalElement('documents')),
            workflowUsers: self::parseWorkflowUsers($root->optionalElement('workflow_users')),
        );
    }

    private static function loadDocument(string $xml): DOMDocument
    {
        if (trim($xml) === '') {
            throw new InvalidWebhookException('The webhook body is empty.');
        }

        // SigningHub reports do not require a document type definition. Reject
        // it explicitly so local and remote external entities cannot be used.
        if (preg_match('/<!DOCTYPE/i', $xml) === 1) {
            throw new InvalidWebhookException('Document type definitions are not allowed in webhook XML.');
        }

        $document = new DOMDocument();
        $document->resolveExternals = false;
        $document->substituteEntities = false;
        $document->validateOnParse = false;
        $previousUseInternalErrors = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_NOCDATA);
            $errors = libxml_get_errors();
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }

        if ($loaded !== true || $document->documentElement === null) {
            $detail = isset($errors[0]) ? trim($errors[0]->message) : 'unknown XML error';

            throw new InvalidWebhookException(sprintf('The webhook body is not valid XML: %s', $detail));
        }

        return $document;
    }

    private static function parseAction(WebhookXmlElement $action): WebhookAction
    {
        return new WebhookAction(
            raw_type: $action->requiredString('type'),
            date_time: $action->requiredString('date_time'),
            performed_by: $action->requiredString('performed_by'),
        );
    }

    /**
     * @return list<WebhookDocument>
     */
    private static function parseDocuments(?WebhookXmlElement $documents): array
    {
        if ($documents === null) {
            return [];
        }

        return array_map(
            static fn (WebhookXmlElement $document): WebhookDocument => new WebhookDocument(
                document_id: $document->requiredInteger('document_id'),
                document_name: $document->requiredString('document_name'),
                document_type: $document->requiredString('document_type'),
                document_order: $document->requiredInteger('document_order'),
                document_source: $document->optionalString('document_source'),
                document_pages: $document->optionalInteger('document_pages'),
            ),
            $documents->elements('document'),
        );
    }

    /**
     * @return list<WebhookWorkflowUser>
     */
    private static function parseWorkflowUsers(?WebhookXmlElement $workflowUsers): array
    {
        if ($workflowUsers === null) {
            return [];
        }

        return array_map(
            static fn (WebhookXmlElement $user): WebhookWorkflowUser => new WebhookWorkflowUser(
                order: $user->requiredInteger('order'),
                user_name: $user->requiredString('user_name'),
                user_email: $user->requiredString('user_email'),
                raw_process_status: $user->requiredString('process_status'),
                processed_on: $user->optionalString('processed_on'),
                processed_as: $user->optionalString('processed_as'),
                signing_order: $user->optionalInteger('signing_order'),
            ),
            $workflowUsers->elements('workflow_user'),
        );
    }
}
