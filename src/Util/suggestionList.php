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
        $distance = levenshtein($input, $options[$i]);
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
