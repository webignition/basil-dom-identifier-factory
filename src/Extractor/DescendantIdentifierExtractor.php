<?php

declare(strict_types=1);

namespace webignition\BasilDomIdentifierFactory\Extractor;

class DescendantIdentifierExtractor
{
    private const PARENT_PREFIX = '$"{{ ';
    private const PARENT_SUFFIX = ' }}';

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

        $childReference = ltrim($childIdentifier, '$"');

        return self::PARENT_PREFIX . $parentIdentifier . self::PARENT_SUFFIX . ' ' . $childReference;
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

        $parentReference = self::PARENT_PREFIX . $parentIdentifier . self::PARENT_SUFFIX;
        $childReference =  mb_substr($string, mb_strlen($parentReference) + 1);
        $childReferenceAsIdentifier =  '$"' . $childReference;

        $childIdentifier = $this->elementIdentifierExtractor->extractIdentifier($childReferenceAsIdentifier);
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

        if (false === $characters || ['$', '"', '{', '{', ' '] === $characters) {
            return null;
        }

        $position = null;
        $depth = 0;

        $parentPrefixLength = strlen(self::PARENT_PREFIX);
        $parentSuffixLength = strlen(self::PARENT_SUFFIX);

        $prefixPreviousCharacters = implode('', array_slice($characters, 0, $parentPrefixLength));
        $suffixPreviousCharacters = mb_substr($prefixPreviousCharacters, 0, $parentSuffixLength);
        $characters = array_slice($characters, $parentPrefixLength);

        foreach ($characters as $index => $character) {
            if (self::PARENT_PREFIX === $prefixPreviousCharacters) {
                $depth++;
            }

            if (self::PARENT_SUFFIX === $suffixPreviousCharacters) {
                $depth--;
            }

            if ($depth === 0) {
                return $index;
            }

            $prefixPreviousCharacters .= $character;
            $prefixPreviousCharacters = mb_substr($prefixPreviousCharacters, 1);
            $suffixPreviousCharacters = mb_substr($prefixPreviousCharacters, 0, $parentSuffixLength);
        }

        return null;
    }

    private function unwrap(string $wrappedIdentifier): string
    {
        $prefixLength = strlen(self::PARENT_PREFIX);
        $suffixLength = strlen(self::PARENT_SUFFIX);

        return mb_substr(
            $wrappedIdentifier,
            $prefixLength,
            mb_strlen($wrappedIdentifier) - $prefixLength - $suffixLength
        );
    }
}
