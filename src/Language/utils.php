<?php

namespace Digia\GraphQL\Language;

/**
 * Multi-byte compatible `chr`.
 *
 * Based on the Symfony's Mbstring polyfills.
 *
 * @param int $code
 * @return string
 */
function mbChr(int $code)
{
    if (0x80 > $code %= 0x200000) {
        return \chr($code);
    }
    if (0x800 > $code) {
        return \chr(0xC0 | $code >> 6) . \chr(0x80 | $code & 0x3F);
    }
    if (0x10000 > $code) {
        return \chr(0xE0 | $code >> 12) . \chr(0x80 | $code >> 6 & 0x3F) . \chr(0x80 | $code & 0x3F);
    }

    return \chr(0xF0 | $code >> 18) . \chr(0x80 | $code >> 12 & 0x3F) . \chr(0x80 | $code >> 6 & 0x3F) . \chr(0x80 | $code & 0x3F);
}

/**
 * Multi-byte compatible `ord`.
 *
 * Based on the Symfony's Mbstring polyfills.
 *
 * @param string $string
 * @param string $encoding
 * @return int
 */
function mbOrd(string $s)
{
    /** @noinspection CallableParameterUseCaseInTypeContextInspection */
    $code = ($s = \unpack('C*', \substr($s, 0, 4))) ? $s[1] : 0;

    if (0xF0 <= $code) {
        return (($code - 0xF0) << 18) + (($s[2] - 0x80) << 12) + (($s[3] - 0x80) << 6) + $s[4] - 0x80;
    }
    if (0xE0 <= $code) {
        return (($code - 0xE0) << 12) + (($s[2] - 0x80) << 6) + $s[3] - 0x80;
    }
    if (0xC0 <= $code) {
        return (($code - 0xC0) << 6) + $s[2] - 0x80;
    }

    return $code;
}

/**
 * @param string $string
 * @param int    $position
 * @return int
 */
function charCodeAt(string $string, int $position): int
{
    return mbOrd(\mb_substr($string, $position, 1, 'UTF-8'));
}

/**
 * @param int $code
 * @return string
 */
function printCharCode(int $code): string
{
    if ($code === 0x0000) {
        return '<EOF>';
    }

    return $code < 0x007F
        // Trust JSON for ASCII.
        ? \json_encode(mbChr($code))
        // Otherwise print the escaped form.
        : '"\\u' . \dechex($code) . '"';
}

/**
 * @param string   $string
 * @param int      $start
 * @param int|null $end
 * @return string
 */
function sliceString(string $string, int $start, int $end = null): string
{
    $length = $end !== null ? $end - $start : \mb_strlen($string) - $start;
    return \mb_substr($string, $start, $length);
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
function isLineTerminator(int $code): bool
{
    return $code === 0x000a || $code === 0x000d;
}

/**
 * @param int $code
 * @return bool
 */
function isSourceCharacter(int $code): bool
{
    return $code < 0x0020 && $code !== 0x0009; // any source character EXCEPT HT (Horizontal Tab)
}

/**
 * @param string $body
 * @param int    $code
 * @param int    $pos
 * @return bool
 */
function isSpread(string $body, int $code, int $pos): bool
{
    return $code === 46 &&
        charCodeAt($body, $pos + 1) === 46 &&
        charCodeAt($body, $pos + 2) === 46; // ...
}

/**
 * @param string $body
 * @param int    $code
 * @param int    $pos
 * @return bool
 */
function isString(string $body, int $code, int $pos): bool
{
    return $code === 34 && charCodeAt($body, $pos + 1) !== 34;
}

/**
 * @param string $body
 * @param int    $code
 * @param int    $pos
 * @return bool
 */
function isTripleQuote(string $body, int $code, int $pos): bool
{
    return $code === 34 &&
        charCodeAt($body, $pos + 1) === 34 &&
        charCodeAt($body, $pos + 2) === 34; // """
}

/**
 * @param string $body
 * @param int    $code
 * @param int    $pos
 * @return bool
 */
function isEscapedTripleQuote(
    string $body,
    int $code,
    int $pos
): bool {
    return $code === 92 &&
        charCodeAt($body, $pos + 1) === 34 &&
        charCodeAt($body, $pos + 2) === 34 &&
        charCodeAt($body, $pos + 3) === 34;
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
    return (\dechex(mbOrd($a)) << 12) |
        (\dechex(mbOrd($b)) << 8) |
        (\dechex(mbOrd($c)) << 4) |
        \dechex(mbOrd($d));
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

/**
 * @param array $location
 * @return array|null
 */
function locationShorthandToArray(array $location): ?array
{
    return isset($location[0], $location[1]) ? ['line' => $location[0], 'column' => $location[1]] : null;
}

/**
 * @param array $locations
 * @return array
 */
function locationsShorthandToArray(array $locations): array
{
    return array_map(function ($shorthand) {
        return locationShorthandToArray($shorthand);
    }, $locations);
}

/**
 * @param array $array
 * @return string
 */
function block(array $array): string
{
    return !empty($array) ? "{\n" . indent(implode("\n", $array)) . "\n}" : '';
}

/**
 * @param string      $start
 * @param null|string $maybeString
 * @param null|string $end
 * @return string
 */
function wrap(string $start, ?string $maybeString = null, ?string $end = null): string
{
    return null !== $maybeString ? ($start . $maybeString . ($end ?? '')) : '';
}

/**
 * @param null|string $maybeString
 * @return string
 */
function indent(?string $maybeString): string
{
    return null !== $maybeString ? '  ' . preg_replace("/\n/", "\n  ", $maybeString) : '';
}

/**
 * @param string $str
 * @return string
 */
function dedent(string $str): string
{
    $trimmed = \preg_replace("/^\n*|[ \t]*$/", '', $str); // Remove leading newline and trailing whitespace
    $matches = [];
    \preg_match("/^[ \t]*/", $trimmed, $matches); // Figure out indent
    $indent = $matches[0];
    return \str_replace($indent, '', $trimmed); // Remove indent
}
