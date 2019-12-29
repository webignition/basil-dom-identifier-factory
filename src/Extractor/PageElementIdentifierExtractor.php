<?php

declare(strict_types=1);

namespace webignition\BasilDomIdentifierFactory\Extractor;

class PageElementIdentifierExtractor
{
    private const VARIABLE_START_CHARACTER = '$';
    private const LOCATOR_DELIMITER = '"';
    private const ESCAPED_LOCATOR_DELIMITER = '\\' . self::LOCATOR_DELIMITER;
    private const POSITION_DELIMITER = ':';
    private const ATTRIBUTE_NAME_DELIMITER = '.';
    private const POSITION_FIRST = 'first';
    private const POSITION_LAST = 'last';

    public static function createExtractor(): PageElementIdentifierExtractor
    {
        return new PageElementIdentifierExtractor();
    }

    public function extractIdentifierString(string $string): ?string
    {
        if (!$this->handles($string)) {
            return null;
        }

        $string = ltrim($string, self::VARIABLE_START_CHARACTER);

        $selector = mb_substr($string, 0, $this->findEndingQuotePosition($string) + 1);
        $remainder = mb_substr($string, mb_strlen($selector));

        if ('' === $remainder) {
            return self::VARIABLE_START_CHARACTER . $selector;
        }

        $identifierString = $selector;

        if (self::POSITION_DELIMITER === $remainder[0]) {
            $remainder = mb_substr($remainder, 1);
            $position = $this->findPosition($remainder);

            if (is_string($position)) {
                $identifierString .= self::POSITION_DELIMITER . (string)$position;
                $remainder = mb_substr($remainder, strlen($position));
            }
        }

        if ($remainder !== '' && self::ATTRIBUTE_NAME_DELIMITER === $remainder[0]) {
            $remainder = mb_substr($remainder, 1);
            $attributeName = $this->findAttributeName($remainder);

            if (null !== $attributeName && '' !== $attributeName) {
                $identifierString .= self::ATTRIBUTE_NAME_DELIMITER . $attributeName;
            }
        }

        return self::VARIABLE_START_CHARACTER . $identifierString;
    }

    private function handles(string $string): bool
    {
        $expectedPrefix = self::VARIABLE_START_CHARACTER . self::LOCATOR_DELIMITER;
        $expectedPrefixLength = strlen($expectedPrefix);
        $length = mb_strlen($string);

        if ($length < $expectedPrefixLength) {
            return false;
        }

        return substr($string, 0, $expectedPrefixLength) === $expectedPrefix;
    }

    private function findEndingQuotePosition(string $string): int
    {
        $currentQuotePosition = 0;
        $endingQuotePosition = null;

        while (null === $endingQuotePosition) {
            $nextQuotePosition = mb_strpos($string, self::LOCATOR_DELIMITER, $currentQuotePosition + 1);

            if (mb_substr($string, $nextQuotePosition - 1, 2) !== self::ESCAPED_LOCATOR_DELIMITER) {
                $endingQuotePosition = $nextQuotePosition;
            } else {
                $currentQuotePosition = mb_strpos($string, self::LOCATOR_DELIMITER, $nextQuotePosition + 1);
            }
        }

        return (int) $endingQuotePosition;
    }

    private function findPosition(string $string): ?string
    {
        $positionContainer = $this->seekAndStopAndSpace($string);
        if (null === $positionContainer) {
            return null;
        }

        $positionParts = explode(self::ATTRIBUTE_NAME_DELIMITER, $positionContainer);
        $position = $positionParts[0];

        if (self::POSITION_FIRST === $position) {
            return self::POSITION_FIRST;
        }

        if (self::POSITION_LAST === $position) {
            return self::POSITION_LAST;
        }

        $integerPosition = (int) $position;

        return (string) $integerPosition === $position ? $position : null;
    }

    private function findAttributeName(string $string): ?string
    {
        if ('' === $string) {
            return null;
        }

        return $this->seekAndStopAndSpace($string);
    }

    private function seekAndStopAndSpace(string $string): ?string
    {
        $characters = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        if (false === $characters || [] === $characters) {
            return null;
        }

        $content = '';
        $endFound = false;
        $characterIndex = 0;

        while ($endFound === false) {
            $character = $characters[$characterIndex] ?? null;
            if (null === $character || ' ' === $character) {
                $endFound = true;
            } else {
                $content .= $character;
            }

            $characterIndex++;
        }

        return $content;
    }
}
