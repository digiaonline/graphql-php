<?php

namespace Digia\GraphQL\Validation\Rule;

class SupportedRules
{
    /**
     * @var RuleInterface[]
     */
    private static $rules;

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
    ];

    /**
     * @return array
     */
    public static function get(): array
    {
        if (null === self::$rules) {
            self::$rules = [];

            foreach (self::$supportedRules as $className) {
                self::$rules[] = new $className();
            }
        }

        return self::$rules;
    }
}
