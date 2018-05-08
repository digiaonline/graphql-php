<?php

namespace Digia\GraphQL\Test;

/**
 * @param mixed $value
 * @return string
 */
function jsonEncode($value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE);
}

/**
 * @param string $path
 * @return string
 */
function readFileContents(string $path): string
{
    return mb_convert_encoding(file_get_contents($path), 'UTF-8');
}
