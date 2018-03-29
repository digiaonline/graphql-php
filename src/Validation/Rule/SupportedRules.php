<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\GraphQL;

class SupportedRules
{
    /**
     * @var array
     */
    private static $supportedRules = [
        ExecutableDefinitionsRule::class,
        FieldOnCorrectTypeRule::class,
        FragmentsOnCompositeTypesRule::class,
        KnownArgumentNamesRule::class,
        KnownDirectivesRule::class,
        KnownFragmentNamesRule::class,
        KnownTypeNamesRule::class,
        LoneAnonymousOperationRule::class,
        NoFragmentCyclesRule::class,
        NoUndefinedVariablesRule::class,
        NoUnusedFragmentsRule::class,
        NoUnusedVariablesRule::class,
        OverlappingFieldsCanBeMergedRule::class,
        PossibleFragmentSpreadsRule::class,
        ProvidedNonNullArgumentsRule::class,
        ScalarLeafsRule::class,
        SingleFieldSubscriptionsRule::class,
        UniqueArgumentNamesRule::class,
        UniqueDirectivesPerLocationRule::class,
        UniqueFragmentNamesRule::class,
        UniqueVariableNamesRule::class,
        ValuesOfCorrectTypeRule::class,
        VariablesAreInputTypesRule::class,
        VariablesDefaultValueAllowedRule::class,
        VariablesInAllowedPositionRule::class,
    ];

    /**
     * Rules maintain state so they should always new instances.
     *
     * @return array
     */
    public static function build(): array
    {
        $rules = [];

        foreach (self::$supportedRules as $className) {
            $rules[] = GraphQL::make($className);
        }

        return $rules;
    }
}
