<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\AbstractException;

class UndefinedFieldException extends AbstractException
{
    /**
     * UndefinedFieldDefinitionException constructor.
     */
    public function __construct(string $fieldName)
    {
        parent::__construct(\sprintf('Undefined field "%s".', $fieldName));
    }
}
