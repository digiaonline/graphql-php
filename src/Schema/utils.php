<?php

namespace Digia\GraphQL\Schema;

/**
 * @param string $description
 * @param int    $maxLength
 * @return array
 */
function descriptionLines(string $description, int $maxLength): array
{
    $lines         = [];
    $rawLines      = \explode("\n", $description);
    $rawLinesCount = \count($rawLines);

    for ($i = 0; $i < $rawLinesCount; $i++) {
        if ('' === $rawLines[$i]) {
            $lines[] = $rawLines[$i];
            continue;
        }

        // For > 120 character long lines, cut at space boundaries into sublines
        // of ~80 chars.
        $subLines = breakLine($rawLines[$i], $maxLength);
        for ($j = 0; $j < \count($subLines); $j++) {
            $lines[] = $subLines[$j];
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
    return \preg_replace('/"""/g', '\\"""', $line);
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
 * @param string $glue
 * @param array  $items
 * @return string
 */
function printArray(string $glue, array $items): string
{
    return \implode($glue, $items);
}
