<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Validation\ValidationContext;

interface RuleInterface
{
    /**
     * @param ValidationContext $context
     * @return $this
     */
    public function setContext(ValidationContext $context);
}
