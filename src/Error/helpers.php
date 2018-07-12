<?php

namespace Digia\GraphQL\Error;

use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Language\SourceLocation;

// Format error

/**
 * @param GraphQLException|null $error
 * @return array
 * @throws InvariantException
 */
function formatError(?GraphQLException $error): array
{
    if (null === $error) {
        throw new InvariantException('Received null error.');
    }

    return [
        'message'   => $error->getMessage(),
        'locations' => $error->getLocationsAsArray(),
        'path'      => $error->getPath(),
    ];
}

// Print error

/**
 * @param GraphQLException $error
 * @return string
 */
function printError(GraphQLException $error): string
{
    $printedLocations = [];
    $nodes            = $error->getNodes();

    if (!empty($nodes)) {
        foreach ($nodes as $node) {
            $location = $node->getLocation();
            if (null !== $location) {
                $printedLocations[] = highlightSourceAtLocation(
                    $location->getSource(),
                    SourceLocation::fromSource($location->getSource(), $location->getStart())
                );
            }
        }
    } elseif ($error->hasSource() && $error->hasLocations()) {
        foreach ($error->getLocations() as $location) {
            $printedLocations[] = highlightSourceAtLocation($error->getSource(), $location);
        }
    }

    return empty($printedLocations)
        ? $error->getMessage()
        : \implode("\n\n", \array_merge([$error->getMessage()], $printedLocations)) . "\n";
}

/**
 * @param Source         $source
 * @param SourceLocation $location
 * @return string
 */
function highlightSourceAtLocation(Source $source, SourceLocation $location): string
{
    $line           = $location->getLine();
    $locationOffset = $source->getLocationOffset();
    $lineOffset     = $locationOffset->getLine() - 1;
    $columnOffset   = getColumnOffset($source, $location);
    $contextLine    = $line + $lineOffset;
    $contextColumn  = $location->getColumn() + $columnOffset;
    $prevLineNum    = (string)($contextLine - 1);
    $lineNum        = (string)$contextLine;
    $nextLineNum    = (string)($contextLine + 1);
    $padLen         = \mb_strlen($nextLineNum);
    $lines          = \preg_split("/\r\n|[\n\r]/", $source->getBody());
    $lines[0]       = whitespace($locationOffset->getColumn() - 1) . $lines[0];
    $outputLines    = [
        \sprintf('%s (%s:%s)', $source->getName(), $contextLine, $contextColumn),
        $line >= 2 ? leftPad($padLen, $prevLineNum) . ': ' . $lines[$line - 2] : null,
        leftPad($padLen, $lineNum) . ': ' . $lines[$line - 1],
        whitespace(2 + $padLen + $contextColumn - 1) . '^',
        $line < \count($lines) ? leftPad($padLen, $nextLineNum) . ': ' . $lines[$line] : null,
    ];

    return \implode("\n", \array_filter($outputLines, function ($line) {
        return null !== $line;
    }));
}

/**
 * @param Source         $source
 * @param SourceLocation $location
 * @return int
 */
function getColumnOffset(Source $source, SourceLocation $location): int
{
    return $location->getLine() === 1 ? $source->getLocationOffset()->getColumn() - 1 : 0;
}

/**
 * @param int $length
 * @return string
 */
function whitespace(int $length): string
{
    return \str_repeat(' ', $length);
}

/**
 * @param int    $length
 * @param string $str
 * @return string
 */
function leftPad(int $length, string $str): string
{
    return whitespace($length - \mb_strlen($str)) . $str;
}
