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
    if (!empty($suggestedTypeNames)) {
        return $message . ' ' . sprintf(
                'Did you mean to use an inline fragment on %s?',
                quotedOrList($suggestedTypeNames)
            );
    }
    if (!empty($suggestedFieldNames)) {
        return $message . ' ' . sprintf('Did you mean %s?', quotedOrList($suggestedFieldNames));
    }
    return $message;
}

/**
 * @param TypeInterface $type
 * @return string
 */
function inlineFragmentOnNonCompositeMessage(string $typeName): string
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

/**
 * @param string $argumentName
 * @param string $fieldName
 * @param string $typeName
 * @param array  $suggestedArguments
 * @return string
 */
function unknownArgumentMessage(
    string $argumentName,
    string $fieldName,
    string $typeName,
    array $suggestedArguments
): string {
    $message = sprintf('Unknown argument "%s" on field "%s" of type "%s".', $argumentName, $fieldName, $typeName);
    if (!empty($suggestedArguments)) {
        return $message . ' ' . sprintf('Did you mean %s', quotedOrList($suggestedArguments));
    }
    return $message;
}

/**
 * @param string $argumentName
 * @param string $directiveName
 * @param array  $suggestedArguments
 * @return string
 */
function unknownDirectiveArgumentMessage(string $argumentName, string $directiveName, array $suggestedArguments): string
{
    $message = sprintf('Unknown argument "%s" on directive "@%s".', $argumentName, $directiveName);
    if (!empty($suggestedArguments)) {
        return $message . ' ' . sprintf('Did you mean %s', quotedOrList($suggestedArguments));
    }
    return $message;
}

/**
 * @param string $directiveName
 * @return string
 */
function unknownDirectiveMessage(string $directiveName): string
{
    return sprintf('Unknown directive "%s".', $directiveName);
}

/**
 * @param string $directiveName
 * @param string $location
 * @return string
 */
function misplacedDirectiveMessage(string $directiveName, string $location): string
{
    return sprintf('Directive "%s" may not be used on %s.', $directiveName, $location);
}

/**
 * @param string $fragmentName
 * @return string
 */
function unknownFragmentMessage(string $fragmentName): string
{
    return sprintf('Unknown fragment "%s".', $fragmentName);
}

/**
 * @param string $typeName
 * @param array  $suggestedTypes
 * @return string
 */
function unknownTypeMessage(string $typeName, array $suggestedTypes): string
{
    $message = sprintf('Unknown type "%s".', $typeName);
    if (!empty($suggestedTypes)) {
        return $message . ' ' . sprintf('Did you mean %s?', quotedOrList($suggestedTypes));
    }
    return $message;
}

/**
 * @return string
 */
function anonymousOperationNotAloneMessage(): string
{
    return 'This anonymous operation must be the only defined operation.';
}

/**
 * @param string $fragmentName
 * @param array  $spreadNames
 * @return string
 */
function fragmentCycleMessage(string $fragmentName, array $spreadNames): string
{
    $via = !empty($spreadNames) ? ' via ' . implode(', ', $spreadNames) : '';
    return sprintf('Cannot spread fragment "%s" within itself%s.', $fragmentName, $via);
}
