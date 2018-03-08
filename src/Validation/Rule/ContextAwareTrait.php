<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Validation\ValidationContext;

trait ContextAwareTrait
{
    /**
     * @var ValidationContext
     */
    protected $context;

    /**
     * @return ValidationContext
     */
    public function getContext(): ValidationContext
    {
        return $this->context;
    }

    /**
     * @param ValidationContext $context
     * @return $this
     */
    public function setContext(ValidationContext $context)
    {
        $this->context = $context;
        return $this;
    }
}
