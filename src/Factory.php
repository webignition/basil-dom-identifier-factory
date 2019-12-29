<?php

declare(strict_types=1);

namespace webignition\BasilDomIdentifierFactory;

use webignition\BasilDomIdentifier\DomIdentifier;
use webignition\BasilDomIdentifierFactory\Extractor\DescendantExtractor;
use webignition\BasilDomIdentifierFactory\Extractor\PageElementIdentifierExtractor;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\QuotedStringValueExtractor\QuotedStringValueExtractor;

class Factory
{
    private const POSITION_FIRST = 'first';
    private const POSITION_LAST = 'last';
    private const POSITION_PATTERN = ':(-?[0-9]+|first|last)';
    private const POSITION_REGEX = '/' . self::POSITION_PATTERN . '$/';

    private const POSITION_LABEL_MAP = [
        self::POSITION_FIRST => 1,
        self::POSITION_LAST => -1,
    ];

    private $pageElementIdentifierExtractor;
    private $descendantExtractor;
    private $identifierTypeAnalyser;
    private $quotedStringValueExtractor;

    public function __construct(
        PageElementIdentifierExtractor $pageElementIdentifierExtractor,
        DescendantExtractor $descendantExtractor,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        QuotedStringValueExtractor $quotedStringValueExtractor
    ) {
        $this->pageElementIdentifierExtractor = $pageElementIdentifierExtractor;
        $this->descendantExtractor = $descendantExtractor;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->quotedStringValueExtractor = $quotedStringValueExtractor;
    }

    public static function createFactory(): Factory
    {
        return new Factory(
            PageElementIdentifierExtractor::createExtractor(),
            DescendantExtractor::createExtractor(),
            new IdentifierTypeAnalyser(),
            QuotedStringValueExtractor::createExtractor()
        );
    }

    public function createFromIdentifierString(string $identifierString): ?DomIdentifier
    {
        $pageElementIdentifier = $this->pageElementIdentifierExtractor->extractIdentifierString($identifierString);
        if (is_string($pageElementIdentifier)) {
            return $this->createFromPageElementIdentifierString($pageElementIdentifier);
        }

        $descendantIdentifier = $this->descendantExtractor->extract($identifierString);
        if (is_string($descendantIdentifier)) {
            return $this->createFromDescendantIdentifierString($descendantIdentifier);
        }

        return null;
    }

    private function createFromPageElementIdentifierString(string $identifierString): DomIdentifier
    {
        $elementIdentifier = $identifierString;
        $attributeName = '';

        if ($this->identifierTypeAnalyser->isAttributeIdentifier($identifierString)) {
            $attributeName = $this->findAttributeName($identifierString);
            $elementIdentifier = $this->findElementIdentifier($identifierString, $attributeName);
        }

        $position = $this->findPosition($elementIdentifier);

        $quotedElementLocatorReference = $this->findElementLocatorReference($elementIdentifier);

        $elementLocatorString = $this->quotedStringValueExtractor->getValue(
            ltrim($quotedElementLocatorReference, '$')
        );

        $identifier = new DomIdentifier($elementLocatorString, $position);

        if ('' !== $attributeName) {
            $identifier = $identifier->withAttributeName($attributeName);
        }

        return $identifier;
    }

    private function createFromDescendantIdentifierString(string $identifierString): DomIdentifier
    {
        $parentIdentifier = $this->descendantExtractor->extractParentIdentifier($identifierString);
        $childIdentifier = $this->descendantExtractor->extractChildIdentifier($identifierString);

        $childDomIdentifier = $this->createFromIdentifierString($childIdentifier);
        $parentDomIdentifier = $this->createFromIdentifierString($parentIdentifier);

        $childDomIdentifier = $childDomIdentifier->withParentIdentifier($parentDomIdentifier);

        return $childDomIdentifier;
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
}
