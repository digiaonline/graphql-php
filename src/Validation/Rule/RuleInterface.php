<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Validation\ValidationContextInterface;

interface RuleInterface
{
    /**
     * @param ValidationContextInterface $context
     * @return $this
     */
    public function setValidationContext(ValidationContextInterface $context);
}
