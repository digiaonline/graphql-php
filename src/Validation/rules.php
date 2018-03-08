<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Validation\Rule\ExecutableDefinitionRule;

/**
 * @return array
 */
function specifiedRules(): array
{
    return [
        GraphQL::get(ExecutableDefinitionRule::class),
    ];
}
