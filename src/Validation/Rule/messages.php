<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\quotedOrList;

/**
 * @param string $definitionName
 * @return string
 */
function nonExecutableDefinitionMessage(string $definitionName): string
{
    return sprintf('The %s definition is not executable.', $definitionName);
}

/**
 * @param string $fieldName
 * @param string $type
 * @param array  $suggestedTypeNames
 * @param array  $suggestedFieldNames
 * @return string
 */
function undefinedFieldMessage(
    string $fieldName,
    string $type,
    array $suggestedTypeNames,
    array $suggestedFieldNames
): string {
    $message = sprintf('Cannot query field "%s" on type "%s".', $fieldName, $type);
    if (count($suggestedTypeNames) !== 0) {
        return $message . ' ' . sprintf(
                'Did you mean to use an inline fragment on %s?',
                quotedOrList($suggestedTypeNames)
            );
    }
    if (count($suggestedFieldNames) !== 0) {
        return $message . ' ' . sprintf('Did you mean %s?', quotedOrList($suggestedFieldNames));
    }
    return $message;
}

/**
 * @param TypeInterface $type
 * @return string
 */
function inlineFragmentOnNonCompositeErrorMessage(string $typeName): string
{
    return sprintf('Fragment cannot condition on non composite type "%s".', $typeName);
}

/**
 * @param string $fragmentName
 * @param string $typeName
 * @return string
 */
function fragmentOnNonCompositeMessage(string $fragmentName, string $typeName): string
{
    return sprintf('Fragment "%s" cannot condition on non composite type "%s".', $fragmentName, $typeName);
}
