<?php

namespace Digia\GraphQL\SchemaValidation\Rule;

use Digia\GraphQL\SchemaValidation\ValidationContextInterface;

interface RuleInterface
{
    /**
     * @param ValidationContextInterface $context
     * @return $this
     */
    public function setContext(ValidationContextInterface $context);

    /**
     *
     */
    public function evaluate(): void;
}
