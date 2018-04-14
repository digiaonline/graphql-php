<?php

namespace Digia\GraphQL\Language;

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
        ? \json_encode(\mb_chr($code))
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
 * @param string $value
 * @return bool
 */
function isOperation(string $value): bool
{
    return $value === 'query' || $value === 'mutation' || $value === 'subscription';
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

/**
 * @param mixed $value
 * @param bool  $isDescription
 * @return null|string|string[]
 */
function printBlockString($value, bool $isDescription)
{
    $escaped = \preg_replace('/"""/g', '\\"""', $value);
    return $value{0} === ' ' || ($value{0} === "\t" && false === strpos($value, "\n"))
        ? '"""' . \preg_replace('/"$/', "\"\n", $escaped) . '"""'
        : '"""' . $isDescription ? $escaped : indent($escaped) . "\n" . '"""';
}
