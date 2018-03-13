<?php

namespace Digia\GraphQL\Validation\Rule;

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
    ];

    /**
     * @return array
     */
    public static function getNew(): array
    {
        $rules = [];

        // Rules maintain state so they should always be re-instantiated.
        foreach (self::$supportedRules as $className) {
            $rules[] = new $className();
        }

        return $rules;
    }
}
