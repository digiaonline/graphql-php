<?php

namespace Digia\GraphQL\Language;

/**
 * @param string $string
 * @param int    $position
 * @return int
 */
function charCodeAt(string $string, int $position): int
{
    return ord($string[$position]);
}

/**
 * @param int $code
 * @return string
 */
function printCharCode(int $code): string
{
    if ($code === null) {
        return '<EOF>';
    }
    return $code < 0x007F
        // Trust JSON for ASCII.
        ? json_encode(chr($code))
        // Otherwise print the escaped form.
        : '"\\u' . dechex($code) . '"';
}

/**
 * @param string   $string
 * @param int      $start
 * @param int|null $end
 * @return string
 */
function sliceString(string $string, int $start, int $end = null): string
{
    $length = $end !== null ? $end - $start : mb_strlen($string) - $start;
    return mb_substr($string, $start, $length);
}

/**
 * @param int $code
 * @return bool
 */
function isLetter(int $code): bool
{
    return ($code >= 65 && $code <= 90) || ($code >= 97 && $code <= 122); // a-z or A-Z
}

/**
 * @param int $code
 * @return bool
 */
function isNumber(int $code): bool
{
    return $code >= 48 && $code <= 57; // 0-9
}

/**
 * @param int $code
 * @return bool
 */
function isUnderscore(int $code): bool
{
    return $code === 95; // _
}

/**
 * @param int $code
 * @return bool
 */
function isAlphaNumeric(int $code): bool
{
    return isLetter($code) || isNumber($code) || isUnderscore($code);
}

/**
 * @param int $code
 * @return bool
 */
function isSourceCharacter(int $code): bool
{
    return $code < 0x0020 && $code !== 0x0009 && $code !== 0x000a && $code !== 0x000d;
}

/**
 * Converts four hexidecimal chars to the integer that the
 * string represents. For example, uniCharCode('0','0','0','f')
 * will return 15, and uniCharCode('0','0','f','f') returns 255.
 *
 * @param string $a
 * @param string $b
 * @param string $c
 * @param string $d
 * @return string
 */
function uniCharCode(string $a, string $b, string $c, string $d): string
{
    return (dechex(ord($a)) << 12) | (dechex(ord($b)) << 8) | (dechex(ord($c)) << 4) | dechex(ord($d));
}

/**
 * Produces the value of a block string from its parsed raw value, similar to
 * Coffeescript's block string, Python's docstring trim or Ruby's strip_heredoc.
 * This implements the GraphQL spec's BlockStringValue() static algorithm.
 *
 * @param string $rawString
 * @return string
 */
function blockStringValue(string $rawString): string
{
    $lines     = preg_split("/\r\n|[\n\r]/", $rawString);
    $lineCount = count($lines);

    $commonIndent = null;

    for ($i = 1; $i < $lineCount; $i++) {
        $line       = $lines[$i];
        $lineLength = mb_strlen($line);
        $indent     = leadingWhitespace($line);

        if ($indent < $lineLength && ($commonIndent === null || $indent < $commonIndent)) {
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

    while ($lineCount > 0 && isBlank($lines[0])) {
        array_shift($lines);
    }

    while ($lineCount > 0 && isBlank($lines[$lineCount - 1])) {
        array_pop($lines);
    }

    return implode("\n", $lines);
}

/**
 * @param string $string
 * @return int
 */
function leadingWhitespace(string $string): int
{
    $i      = 0;
    $length = mb_strlen($string);
    while ($i < $length && ($string[$i] === ' ' || $string[$i] === "\t")) {
        $i++;
    }
    return $i;
}

/**
 * @param string $string
 * @return bool
 */
function isBlank(string $string): bool
{
    return leadingWhitespace($string) === mb_strlen($string);
}

/**
 * @param string $value
 * @return bool
 */
function isOperation(string $value): bool
{
    // TODO: Benchmark
    return \in_array($value, ['query', 'mutation', 'subscription'], true);
}
