<?php

namespace Digia\GraphQL\Schema\Validation\Rule;

use Digia\GraphQL\Schema\Validation\ValidationContextInterface;

abstract class AbstractRule implements RuleInterface
{
    /**
     * @var ValidationContextInterface
     */
    protected $context;

    /**
     * @param ValidationContextInterface $context
     * @return $this
     */
    public function setContext(ValidationContextInterface $context)
    {
        $this->context = $context;
        return $this;
    }
}
