<?php

namespace Digia\GraphQL\SchemaValidator\Rule;

use Digia\GraphQL\SchemaValidator\ValidationContextInterface;

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
