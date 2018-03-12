<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Validation\Rule\ExecutableDefinitionsRule;
use Digia\GraphQL\Validation\Rule\FieldOnCorrectTypeRule;
use Digia\GraphQL\Validation\Rule\FragmentsOnCompositeTypesRule;
use Digia\GraphQL\Validation\Rule\KnownArgumentNamesRule;
use Digia\GraphQL\Validation\Rule\KnownDirectivesRule;
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
        GraphQL::get(PossibleFragmentSpreadsRule::class),
    ];
}
