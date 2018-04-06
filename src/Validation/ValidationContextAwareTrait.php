<?php

namespace Digia\GraphQL\Validation;

trait ValidationContextAwareTrait
{

    /**
     * @var ValidationContextInterface
     */
    protected $context;

    /**
     * @return ValidationContextInterface
     */
    public function getContext(): ValidationContextInterface
    {
        return $this->context;
    }

    /**
     * @param ValidationContextInterface $context
     *
     * @return $this
     */
    public function setContext(ValidationContextInterface $context)
    {
        $this->context = $context;

        return $this;
    }
}
