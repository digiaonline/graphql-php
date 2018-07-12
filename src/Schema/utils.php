<?php

namespace Digia\GraphQL\Schema;

/**
 * @param string $description
 * @param int    $maxLength
 * @return array
 */
function descriptionLines(string $description, int $maxLength): array
{
    $lines    = [];
    $rawLines = \explode("\n", $description);

    foreach ($rawLines as $rawLine) {
        if ('' === $rawLine) {
            $lines[] = $rawLine;
            continue;
        }

        // For > 120 character long lines, cut at space boundaries into sublines
        // of ~80 chars.
        $subLines = breakLine($rawLine, $maxLength);

        foreach ($subLines as $subLine) {
            $lines[] = $subLine;
        }
    }

    return $lines;
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

    $pos        = $maxLength - 40;
    $parts      = \preg_split("/((?: |^).{15,{$pos}}(?= |$))/", $line);
    $partsCount = \count($parts);

    if ($partsCount < 4) {
        return [$line];
    }

    $subLines = [$parts[0] . $parts[1] . $parts[2]];

    for ($i = 3; $i < $partsCount; $i++) {
        $subLines[] = \array_slice($parts[$i], 1) . $parts[$i + 1];
    }

    return $subLines;
}

/**
 * @param string $line
 * @return string
 */
function escapeQuote(string $line): string
{
    return \preg_replace('/"""/', '\\"""', $line);
}

/**
 * @param array $lines
 * @return string
 */
function printLines(array $lines): string
{
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
