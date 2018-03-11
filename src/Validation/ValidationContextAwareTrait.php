<?php

namespace Digia\GraphQL\Validation;

trait ValidationContextAwareTrait
{
    /**
     * @var ValidationContextInterface
     */
    protected $validationContext;

    /**
     * @return ValidationContextInterface
     */
    public function getValidationContext(): ValidationContextInterface
    {
        return $this->validationContext;
    }

    /**
     * @param ValidationContextInterface $validationContext
     * @return $this
     */
    public function setValidationContext(ValidationContextInterface $validationContext)
    {
        $this->validationContext = $validationContext;
        return $this;
    }
}
