<?php

namespace Digia\GraphQL\Schema\Validation\Rule;

use Digia\GraphQL\Schema\Validation\ValidationContextInterface;

interface RuleInterface
{

    /**
     * @param ValidationContextInterface $context
     *
     * @return $this
     */
    public function setContext(ValidationContextInterface $context);

    /**
     *
     */
    public function evaluate(): void;
}
