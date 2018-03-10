<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Validation\Rule\ExecutableDefinitionRule;
use Digia\GraphQL\Validation\Rule\FieldOnCorrectTypeRule;
use Digia\GraphQL\Validation\Rule\FragmentsOnCompositeTypesRule;
use Digia\GraphQL\Validation\Rule\KnownArgumentNamesRule;
use Digia\GraphQL\Validation\Rule\KnownDirectivesRule;
use Digia\GraphQL\Validation\Rule\KnownFragmentNamesRule;
use Digia\GraphQL\Validation\Rule\KnownTypeNamesRule;

/**
 * @return array
 */
function specifiedRules(): array
{
    return [
        GraphQL::get(ExecutableDefinitionRule::class),
        GraphQL::get(FieldOnCorrectTypeRule::class),
        GraphQL::get(FragmentsOnCompositeTypesRule::class),
        GraphQL::get(KnownArgumentNamesRule::class),
        GraphQL::get(KnownDirectivesRule::class),
        GraphQL::get(KnownFragmentNamesRule::class),
        GraphQL::get(KnownTypeNamesRule::class),
    ];
}
