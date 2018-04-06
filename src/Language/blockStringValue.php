<?php

namespace Digia\GraphQL\Language;

/**
 * Produces the value of a block string from its parsed raw value, similar to
 * Coffeescript's block string, Python's docstring trim or Ruby's strip_heredoc.
 * This implements the GraphQL spec's BlockStringValue() static algorithm.
 *
 * @param string $rawString
 *
 * @return string
 */
function blockStringValue(string $rawString): string
{
    $lines = preg_split("/\r\n|[\n\r]/", $rawString);
    $lineCount = count($lines);

    $commonIndent = null;

    for ($i = 1; $i < $lineCount; $i++) {
        $line = $lines[$i];
        $indent = leadingWhitespace($line);

        if ($indent < mb_strlen($line) && ($commonIndent === null || $indent < $commonIndent)) {
            $commonIndent = $indent;

            if ($commonIndent === 0) {
                break;
            }
        }
    }

    if ($commonIndent > 0) {
        for ($i = 1; $i < $lineCount; $i++) {
            $lines[$i] = sliceString($lines[$i], $commonIndent);
        }
    }

    while (count($lines) > 0 && isBlank($lines[0])) {
        array_shift($lines);
    }

    while (($lineCount = count($lines)) > 0 && isBlank($lines[$lineCount - 1])) {
        array_pop($lines);
    }

    return implode("\n", $lines);
}

/**
 * @param string $string
 *
 * @return int
 */
function leadingWhitespace(string $string): int
{
    $i = 0;
    $length = mb_strlen($string);
    while ($i < $length && ($string[$i] === ' ' || $string[$i] === "\t")) {
        $i++;
    }

    return $i;
}

/**
 * @param string $string
 *
 * @return bool
 */
function isBlank(string $string): bool
{
    return leadingWhitespace($string) === mb_strlen($string);
}
