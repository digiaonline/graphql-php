<?php

namespace Digia\GraphQL\Schema;

/**
 * @param string $description
 * @param int    $maxLength
 * @return array
 */
function descriptionLines(string $description, int $maxLength): array
{
    // Map over the description lines and merge them into a flat array.
    return \array_merge(...\array_map(function (string $line) use ($maxLength) {
        if (\strlen($line) < ($maxLength + 5)) {
            return [$line];
        }

        // For > 120 character long lines, cut at space boundaries into sublines of ~80 chars.
        return breakLine($line, $maxLength);
    }, \explode("\n", $description)));
}

/**
 * @param string $line
 * @param int    $maxLength
 * @return array
 */
function breakLine(string $line, int $maxLength): array
{
    if (\strlen($line) < ($maxLength + 5)) {
        return [$line];
    }

    $endPos = $maxLength - 40;

    return \array_map('trim', \preg_split(
        "/((?: |^).{15,{$endPos}}(?= |$))/",
        $line,
        0,
        PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
    ));
}

/**
 * @param string $line
 * @return string
 */
function escapeQuotes(string $line): string
{
    return \strtr($line, ['"""' => '\\"""', '`' => '\`']);
}

/**
 * @param array $lines
 * @return string
 */
function printLines(array $lines): string
{
    // Don't print empty lines
    $lines = \array_filter($lines, function (string $line) {
        return $line !== '';
    });

    return printArray("\n", $lines);
}

/**
 * @param array $fields
 * @return string
 */
function printInputFields(array $fields): string
{
    return '(' . printArray(', ', $fields) . ')';
}

/**
 * @param string $glue
 * @param array  $items
 * @return string
 */
function printArray(string $glue, array $items): string
{
    return \implode($glue, $items);
}
