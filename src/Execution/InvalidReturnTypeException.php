<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLException;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;
use function Digia\GraphQL\Util\toString;

class InvalidReturnTypeException extends GraphQLException
{

    /**
     * InvalidReturnTypeException constructor.
     *
     * @param TypeInterface $returnType
     * @param mixed         $result
     * @param array         $fieldNodes
     */
    public function __construct(TypeInterface $returnType, $result, array $fieldNodes = [])
    {
        parent::__construct(
            \sprintf('Expected value of type "%s" but got: %s.', (string)$returnType, toString($result)),
            $fieldNodes
        );
    }
}
