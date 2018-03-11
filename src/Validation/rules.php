<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Validation\Rule\ExecutableDefinitionsRule;

/**
 * @return array
 */
function specifiedRules(): array
{
    return [
        GraphQL::get(ExecutableDefinitionsRule::class),
    ];
}
