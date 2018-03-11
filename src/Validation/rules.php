<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Validation\Rule\ExecutableDefinitionsRule;
use Digia\GraphQL\Validation\Rule\FieldOnCorrectTypeRule;
use Digia\GraphQL\Validation\Rule\FragmentsOnCompositeTypesRule;
use Digia\GraphQL\Validation\Rule\KnownArgumentNamesRule;
use Digia\GraphQL\Validation\Rule\KnownDirectivesRule;
use Digia\GraphQL\Validation\Rule\KnownFragmentNamesRule;
use Digia\GraphQL\Validation\Rule\KnownTypeNamesRule;
use Digia\GraphQL\Validation\Rule\LoneAnonymousOperationRule;
use Digia\GraphQL\Validation\Rule\NoFragmentCyclesRule;
use Digia\GraphQL\Validation\Rule\NoUndefinedVariablesRule;
use Digia\GraphQL\Validation\Rule\NoUnusedFragmentsRule;
use Digia\GraphQL\Validation\Rule\NoUnusedVariablesRule;
use Digia\GraphQL\Validation\Rule\OverlappingFieldsCanBeMergedRule;
use Digia\GraphQL\Validation\Rule\PossibleFragmentSpreadsRule;

/**
 * @return array
 */
function specifiedRules(): array
{
    return [
        GraphQL::get(ExecutableDefinitionsRule::class),
        GraphQL::get(FieldOnCorrectTypeRule::class),
        GraphQL::get(FragmentsOnCompositeTypesRule::class),
        GraphQL::get(KnownArgumentNamesRule::class),
        GraphQL::get(KnownDirectivesRule::class),
        GraphQL::get(KnownFragmentNamesRule::class),
        GraphQL::get(KnownTypeNamesRule::class),
        GraphQL::get(LoneAnonymousOperationRule::class),
        GraphQL::get(NoFragmentCyclesRule::class),
        GraphQL::get(NoUndefinedVariablesRule::class),
        GraphQL::get(NoUnusedFragmentsRule::class),
        GraphQL::get(NoUnusedVariablesRule::class),
        GraphQL::get(OverlappingFieldsCanBeMergedRule::class),
        GraphQL::get(PossibleFragmentSpreadsRule::class),
    ];
}
