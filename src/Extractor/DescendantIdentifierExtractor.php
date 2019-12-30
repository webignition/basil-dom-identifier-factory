<?php

declare(strict_types=1);

namespace webignition\BasilDomIdentifierFactory\Extractor;

class DescendantIdentifierExtractor
{
    private const PARENT_PREFIX = '{{ ';
    private const PARENT_SUFFIX = ' }}';
    private const PARENT_MATCH_LENGTH = 3;

    private $elementIdentifierExtractor;

    public function __construct(ElementIdentifierExtractor $pageElementIdentifierExtractor)
    {
        $this->elementIdentifierExtractor = $pageElementIdentifierExtractor;
    }

    public static function createExtractor(): DescendantIdentifierExtractor
    {
        return new DescendantIdentifierExtractor(
            ElementIdentifierExtractor::createExtractor()
        );
    }

    public function extractIdentifier(string $string): ?string
    {
        $parentIdentifier = $this->extractParentIdentifier($string);
        if (null === $parentIdentifier) {
            return null;
        }

        $childIdentifier = $this->extractChildIdentifier($string);
        if (null === $childIdentifier) {
            return null;
        }

        return '{{ ' . $parentIdentifier . ' }}' . ' ' . $childIdentifier;
    }

    public function extractParentIdentifier(string $string): ?string
    {
        if (self::PARENT_PREFIX !== substr($string, 0, strlen(self::PARENT_PREFIX))) {
            return null;
        }

        $parentSuffixPosition = $this->findParentSuffixPosition($string);
        if (null === $parentSuffixPosition) {
            return null;
        }

        $parentReference = mb_substr($string, 0, $parentSuffixPosition + strlen(self::PARENT_SUFFIX));
        $parentReferenceIdentifier = $this->unwrap($parentReference);

        if (false === $this->isParentReference($parentReferenceIdentifier)) {
            return null;
        }

        return $parentReferenceIdentifier;
    }

    public function extractChildIdentifier(string $string): ?string
    {
        $parentIdentifier = $this->extractParentIdentifier($string);

        if (null === $parentIdentifier) {
            return null;
        }

        $parentReference = '{{ ' . $parentIdentifier . ' }}';

        $childReference = mb_substr($string, mb_strlen($parentReference) + 1);
        $childIdentifier = $this->elementIdentifierExtractor->extractIdentifier($childReference);

        if (null === $childIdentifier) {
            return null;
        }

        return $childIdentifier;
    }

    private function isParentReference(string $string): bool
    {
        if (null !== $this->extractParentIdentifier($string)) {
            return true;
        }

        if (null !== $this->elementIdentifierExtractor->extractIdentifier($string)) {
            return true;
        }

        return false;
    }

    private function findParentSuffixPosition(string $string): ?int
    {
        $characters = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

        if (false === $characters || ['{', '{', ' '] === $characters) {
            return null;
        }

        $position = null;
        $depth = 0;

        $previousCharacters = implode('', array_slice($characters, 0, self::PARENT_MATCH_LENGTH));
        $characters = array_slice($characters, self::PARENT_MATCH_LENGTH);

        foreach ($characters as $index => $character) {
            if (self::PARENT_PREFIX === $previousCharacters) {
                $depth++;
            }

            if (self::PARENT_SUFFIX === $previousCharacters) {
                $depth--;
            }

            if ($depth === 0) {
                return $index;
            }

            $previousCharacters .= $character;
            $previousCharacters = mb_substr($previousCharacters, 1);
        }

        return null;
    }

    private function unwrap(string $wrappedIdentifier): string
    {
        return mb_substr(
            $wrappedIdentifier,
            self::PARENT_MATCH_LENGTH,
            mb_strlen($wrappedIdentifier) - self::PARENT_MATCH_LENGTH - self::PARENT_MATCH_LENGTH
        );
    }
}
