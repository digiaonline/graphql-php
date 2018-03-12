<?php

namespace Digia\GraphQL\Test\Functional\Validation;

use function Digia\GraphQL\Language\locationShorthandToArray;
use function Digia\GraphQL\Validation\anonymousOperationNotAloneMessage;
use function Digia\GraphQL\Validation\fieldsConflictMessage;
use function Digia\GraphQL\Validation\fragmentCycleMessage;
use function Digia\GraphQL\Validation\fragmentOnNonCompositeMessage;
use function Digia\GraphQL\Validation\misplacedDirectiveMessage;
use function Digia\GraphQL\Validation\missingDirectiveArgumentMessage;
use function Digia\GraphQL\Validation\missingFieldArgumentMessage;
use function Digia\GraphQL\Validation\nonExecutableDefinitionMessage;
use function Digia\GraphQL\Validation\noSubselectionAllowedMessage;
use function Digia\GraphQL\Validation\requiresSubselectionMessage;
use function Digia\GraphQL\Validation\singleFieldOnlyMessage;
use function Digia\GraphQL\Validation\typeIncompatibleAnonymousSpreadMessage;
use function Digia\GraphQL\Validation\typeIncompatibleSpreadMessage;
use function Digia\GraphQL\Validation\undefinedFieldMessage;
use function Digia\GraphQL\Validation\undefinedVariableMessage;
use function Digia\GraphQL\Validation\unknownArgumentMessage;
use function Digia\GraphQL\Validation\unknownDirectiveArgumentMessage;
use function Digia\GraphQL\Validation\unknownDirectiveMessage;
use function Digia\GraphQL\Validation\unknownFragmentMessage;
use function Digia\GraphQL\Validation\unknownTypeMessage;
use function Digia\GraphQL\Validation\unusedFragmentMessage;
use function Digia\GraphQL\Validation\unusedVariableMessage;

function nonExecutableDefinition($definitionName, $location)
{
    return [
        'message'   => nonExecutableDefinitionMessage($definitionName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function undefinedField($field, $type, $suggestedTypes, $suggestsFields, $location)
{
    return [
        'message'   => undefinedFieldMessage($field, $type, $suggestedTypes, $suggestsFields),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function fragmentOnNonComposite($fragmentName, $typeName, $location)
{
    return [
        'message'   => fragmentOnNonCompositeMessage($fragmentName, $typeName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function unknownArgument($argumentName, $fieldName, $typeName, $suggestedArguments, $location)
{
    return [
        'message'   => unknownArgumentMessage($argumentName, $fieldName, $typeName, $suggestedArguments),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function unknownDirectiveArgument($argumentName, $directiveName, $suggestedArguments, $location)
{
    return [
        'message'   => unknownDirectiveArgumentMessage($argumentName, $directiveName, $suggestedArguments),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function unknownDirective($directiveName, $location)
{
    return [
        'message'   => unknownDirectiveMessage($directiveName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function misplacedDirective($directiveName, $directiveLocation, $location)
{
    return [
        'message'   => misplacedDirectiveMessage($directiveName, $directiveLocation),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function unknownFragment($fragmentName, $location)
{
    return [
        'message'   => unknownFragmentMessage($fragmentName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function unknownType($typeName, $suggestedTypes, $location)
{
    return [
        'message'   => unknownTypeMessage($typeName, $suggestedTypes),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function anonymousOperationNotAlone($location)
{
    return [
        'message'   => anonymousOperationNotAloneMessage(),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function fragmentCycle($fragmentName, $spreadNames, array $locations)
{
    return [
        'message'   => fragmentCycleMessage($fragmentName, $spreadNames),
        'locations' => array_map(function ($shorthand) {
            return locationShorthandToArray($shorthand);
        }, $locations),
        'path'      => null,
    ];
}

function undefinedVariable($variableName, $variableLocation, $operationName, $operationLocation)
{
    return [
        'message'   => undefinedVariableMessage($variableName, $operationName),
        'locations' => [
            locationShorthandToArray($variableLocation),
            locationShorthandToArray($operationLocation),
        ],
        'path'      => null,
    ];
}

function unusedFragment($fragmentName, $location)
{
    return [
        'message'   => unusedFragmentMessage($fragmentName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function unusedVariable($variableName, $operationName, $location)
{
    return [
        'message'   => unusedVariableMessage($variableName, $operationName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function fieldConflict($responseName, $reason, $locations)
{
    return [
        'message'   => fieldsConflictMessage($responseName, $reason),
        'locations' => array_map(function ($shorthand) {
            return locationShorthandToArray($shorthand);
        }, $locations),
        'path'      => null,
    ];
}

function typeIncompatibleSpread($fragmentName, $parentType, $fragmentType, $location)
{
    return [
        'message'   => typeIncompatibleSpreadMessage($fragmentName, $parentType, $fragmentType),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function typeIncompatibleAnonymousSpread($parentType, $fragmentType, $location)
{
    return [
        'message'   => typeIncompatibleAnonymousSpreadMessage($parentType, $fragmentType),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function missingFieldArgument($fieldName, $argumentName, $typeName, $location)
{
    return [
        'message'   => missingFieldArgumentMessage($fieldName, $argumentName, $typeName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function missingDirectiveArgument($directiveName, $argumentName, $typeName, $location)
{
    return [
        'message'   => missingDirectiveArgumentMessage($directiveName, $argumentName, $typeName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function noSubselectionAllowed($fieldName, $typeName, $location)
{
    return [
        'message'   => noSubselectionAllowedMessage($fieldName, $typeName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function requiredSubselection($fieldName, $typeName, $location)
{
    return [
        'message'   => requiresSubselectionMessage($fieldName, $typeName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function singleFieldOnly($name, $locations)
{
    return [
        'message'   => singleFieldOnlyMessage($name),
        'locations' => array_map(function ($shorthand) {
            return locationShorthandToArray($shorthand);
        }, $locations),
        'path'      => null,
    ];
}
