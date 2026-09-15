<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen\Webhook;

use DOMElement;
use Koba\DigitaalOndertekenen\Exception\InvalidWebhookException;

/** @internal */
final readonly class WebhookXmlElement
{
    private const XML_SCHEMA_INSTANCE_NAMESPACE = 'http://www.w3.org/2001/XMLSchema-instance';

    private function __construct(
        private DOMElement $element,
        private string $path,
    ) {
    }

    public static function root(DOMElement $element): self
    {
        return new self($element, '/' . $element->localName);
    }

    public function requiredElement(string $name): self
    {
        $element = $this->optionalElement($name);

        if ($element === null) {
            throw new InvalidWebhookException(sprintf(
                'The webhook is missing the required element "%s".',
                $this->childPath($name),
            ));
        }

        return $element;
    }

    public function optionalElement(string $name): ?self
    {
        $elements = $this->matchingChildren($name);

        if (count($elements) > 1) {
            throw new InvalidWebhookException(sprintf(
                'The webhook contains more than one "%s" element.',
                $this->childPath($name),
            ));
        }

        return isset($elements[0])
            ? new self($elements[0], $this->childPath($name))
            : null;
    }

    /**
     * @return list<self>
     */
    public function elements(string $name): array
    {
        return array_map(
            fn (DOMElement $element): self => new self($element, $this->childPath($name)),
            $this->matchingChildren($name),
        );
    }

    public function requiredString(string $name): string
    {
        $value = $this->optionalString($name);

        if ($value === null) {
            throw new InvalidWebhookException(sprintf(
                'The webhook is missing the required value "%s".',
                $this->childPath($name),
            ));
        }

        return $value;
    }

    public function optionalString(string $name): ?string
    {
        $element = $this->optionalElement($name);

        if ($element === null || $element->isNil()) {
            return null;
        }

        $value = trim($element->element->textContent);

        return $value === '' ? null : $value;
    }

    public function requiredInteger(string $name): int
    {
        return $this->integer($name, $this->requiredString($name));
    }

    public function optionalInteger(string $name): ?int
    {
        $value = $this->optionalString($name);

        return $value === null ? null : $this->integer($name, $value);
    }

    /**
     * @return list<DOMElement>
     */
    private function matchingChildren(string $name): array
    {
        $matches = [];

        foreach ($this->element->childNodes as $child) {
            if (
                $child instanceof DOMElement
                && $child->localName === $name
                && $child->namespaceURI === $this->element->namespaceURI
            ) {
                $matches[] = $child;
            }
        }

        return $matches;
    }

    private function isNil(): bool
    {
        $nil = $this->element->getAttributeNS(self::XML_SCHEMA_INSTANCE_NAMESPACE, 'nil');

        return $nil === 'true' || $nil === '1';
    }

    private function integer(string $name, string $value): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new InvalidWebhookException(sprintf(
                'The webhook value "%s" is not an integer.',
                $this->childPath($name),
            ));
        }

        return (int) $value;
    }

    private function childPath(string $name): string
    {
        return $this->path . '/' . $name;
    }
}
