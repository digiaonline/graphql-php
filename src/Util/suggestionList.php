<?php

namespace Digia\GraphQL\Util;

/**
 * @param string $input
 * @param array  $options
 * @return array
 */
function suggestionList(string $input, array $options): array
{
    $optionsByDistance = [];
    $oLength = count($options);
    $inputThreshold = strlen($input) / 2;

    /** @noinspection ForeachInvariantsInspection */
    for ($i = 0; $i < $oLength; $i++) {
        $distance = lexicalDistance($input, $options[$i]);
        $threshold = max($inputThreshold, strlen($options[$i]) / 2, 1);
        if ($distance <= $threshold) {
            $optionsByDistance[$options[$i]] = $distance;
        }
    }

    $result = array_keys($optionsByDistance);

    usort($result, function ($a, $b) use ($optionsByDistance) {
        return $optionsByDistance[$a] - $optionsByDistance[$b];
    });

    return $result;
}

/**
 * @param string $aStr
 * @param string $bStr
 * @return int
 */
function lexicalDistance(string $aStr, string $bStr): int
{
    if ($aStr === $bStr) {
        return 0;
    }

    $d = [];
    $a = strtolower($aStr);
    $b = strtolower($bStr);
    $aLength = strlen($a);
    $bLength = strlen($b);

    if ($a === $b) {
        return 1;
    }

    /** @noinspection ForeachInvariantsInspection */
    for ($i = 0; $i <= $aLength; $i++) {
        $d[$i] = [$i];
    }

    /** @noinspection ForeachInvariantsInspection */
    for ($j = 1; $j <= $bLength; $j++) {
        $d[0][$j] = $i;
    }

    for ($i = 1; $i <= $aLength; $i++) {
        for ($j = 1; $j <= $bLength; $j++) {
            $cost = $a[$i - 1] === $b[$j - 1] ? 0 : 1;

            $d[$i][$j] = min($d[$i - 1][$j] + 1, $d[$i][$j - 1] + 1, $d[$i - 1][$j - 1] + $cost);

            if ($i > 1 && $j > 1 && $a[$i - 1] === $b[$j - 2] && $a[$i - 2] === $b[$j - 1]) {
                $d[$i][$j] = min($d[$i][$j], $d[$i - 2][$j - 2] + $cost);
            }
        }
    }

    return $d[$aLength][$bLength];
}
