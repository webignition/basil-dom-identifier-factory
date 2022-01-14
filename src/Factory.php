<?php

declare(strict_types=1);

namespace webignition\BasilDomIdentifierFactory;

use webignition\BasilValueExtractor\DescendantIdentifierExtractor;
use webignition\BasilValueExtractor\ElementIdentifierExtractor;
use webignition\BasilValueExtractor\VariableValueExtractor;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\QuotedStringValueExtractor\QuotedStringValueExtractor;

class Factory
{
    private const POSITION_FIRST = 'first';
    private const POSITION_LAST = 'last';
    private const POSITION_PATTERN = ':(-?[0-9]+|first|last)';
    private const POSITION_REGEX = '/' . self::POSITION_PATTERN . '$/';

    /**
     * Pattern for characters not allowed in an html attribute name.
     *
     * @see https://html.spec.whatwg.org/multipage/syntax.html#attributes-2
     */
    private const DISALLOWED_ATTRIBUTE_NAME_CHARACTERS_PATTERN = '[^ "\'>\/=]';

    private const POSITION_LABEL_MAP = [
        self::POSITION_FIRST => 1,
        self::POSITION_LAST => -1,
    ];

    private ElementIdentifierExtractor $elementIdentifierExtractor;
    private DescendantIdentifierExtractor $descendantExtractor;
    private QuotedStringValueExtractor $quotedStringValueExtractor;

    public function __construct(
        ElementIdentifierExtractor $pageElementIdentifierExtractor,
        DescendantIdentifierExtractor $descendantExtractor,
        QuotedStringValueExtractor $quotedStringValueExtractor
    ) {
        $this->elementIdentifierExtractor = $pageElementIdentifierExtractor;
        $this->descendantExtractor = $descendantExtractor;
        $this->quotedStringValueExtractor = $quotedStringValueExtractor;
    }

    public static function createFactory(): Factory
    {
        $elementIdentifierExtractor = new ElementIdentifierExtractor();

        return new Factory(
            $elementIdentifierExtractor,
            new DescendantIdentifierExtractor(
                new ElementIdentifierExtractor(),
                new VariableValueExtractor()
            ),
            QuotedStringValueExtractor::createExtractor()
        );
    }

    public function createFromIdentifierString(string $identifierString): ?ElementIdentifierInterface
    {
        $descendantIdentifier = $this->descendantExtractor->extractIdentifier($identifierString);
        if (is_string($descendantIdentifier)) {
            return $this->createFromDescendantIdentifierString($descendantIdentifier);
        }

        $pageElementIdentifier = $this->elementIdentifierExtractor->extract($identifierString);
        if (is_string($pageElementIdentifier)) {
            return $this->createFromPageElementIdentifierString($pageElementIdentifier);
        }

        return null;
    }

    private function createFromPageElementIdentifierString(string $identifierString): ElementIdentifierInterface
    {
        $elementIdentifier = $identifierString;
        $attributeName = '';

        if ($this->isAttributeIdentifierMatch($identifierString)) {
            $attributeName = $this->findAttributeName($identifierString);
            $elementIdentifier = $this->findElementIdentifier($identifierString, $attributeName);
        }

        $position = $this->findPosition($elementIdentifier);

        $quotedElementLocatorReference = $this->findElementLocatorReference($elementIdentifier);

        $elementLocatorString = $this->quotedStringValueExtractor->getValue(
            ltrim($quotedElementLocatorReference, '$')
        );

        return '' === $attributeName
            ? new ElementIdentifier($elementLocatorString, $position)
            : new AttributeIdentifier($elementLocatorString, $attributeName, $position);
    }

    private function createFromDescendantIdentifierString(string $identifierString): ?ElementIdentifierInterface
    {
        $parentIdentifier = $this->descendantExtractor->extractParentIdentifier($identifierString);
        if (null === $parentIdentifier) {
            return null;
        }

        $parentDomIdentifier = $this->createFromIdentifierString($parentIdentifier);
        if (null === $parentDomIdentifier) {
            return null;
        }

        $childIdentifier = $this->descendantExtractor->extractChildIdentifier($identifierString);
        if (null === $childIdentifier) {
            return null;
        }

        $childDomIdentifier = $this->createFromIdentifierString($childIdentifier);
        if (null === $childDomIdentifier) {
            return null;
        }

        return $childDomIdentifier->withParentIdentifier($parentDomIdentifier);
    }

    private function findAttributeName(string $identifierString): string
    {
        $lastDotPosition = (int) mb_strrpos($identifierString, '.');

        return mb_substr($identifierString, $lastDotPosition + 1);
    }

    private function findElementIdentifier(string $identifierString, string $attributeName): string
    {
        return mb_substr(
            $identifierString,
            0,
            mb_strlen($identifierString) - mb_strlen($attributeName) - 1
        );
    }

    private function findPosition(string $identifierString): ?int
    {
        $positionMatches = [];
        preg_match(self::POSITION_REGEX, $identifierString, $positionMatches);

        if ([] === $positionMatches) {
            return null;
        }

        $positionMatch = $positionMatches[0];
        $positionString = ltrim($positionMatch, ':');

        $mappedPosition = self::POSITION_LABEL_MAP[$positionString] ?? null;
        if (is_int($mappedPosition)) {
            return $mappedPosition;
        }

        return (int) $positionString;
    }

    private function findElementLocatorReference(string $elementIdentifier): string
    {
        $positionMatches = [];
        preg_match(self::POSITION_REGEX, $elementIdentifier, $positionMatches);

        if ([] === $positionMatches) {
            return $elementIdentifier;
        }

        $lastPositionDelimiterPosition = (int) mb_strrpos($elementIdentifier, ':');

        return mb_substr($elementIdentifier, 0, $lastPositionDelimiterPosition - 1);
    }

    private function isAttributeIdentifierMatch(string $elementIdentifier): bool
    {
        if (preg_match(self::POSITION_REGEX, $elementIdentifier) > 0) {
            return false;
        }

        if (preg_match('/\$"\.[^.]+$/', $elementIdentifier) > 0) {
            return false;
        }

        $endingWithAttributeRegex = '/\.(' . self::DISALLOWED_ATTRIBUTE_NAME_CHARACTERS_PATTERN . '+)$/';

        return preg_match($endingWithAttributeRegex, $elementIdentifier) > 0;
    }
}
