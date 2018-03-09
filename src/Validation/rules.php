<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Validation\Rule\ExecutableDefinitionRule;
use Digia\GraphQL\Validation\Rule\FieldOnCorrectTypeRule;

/**
 * @return array
 */
function specifiedRules(): array
{
    return [
        GraphQL::get(ExecutableDefinitionRule::class),
        GraphQL::get(FieldOnCorrectTypeRule::class),
    ];
}
