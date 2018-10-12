<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\GraphQLException;

class SyntaxErrorException extends GraphQLException
{
    /**
     * SyntaxErrorException constructor.
     * @param Source $source
     * @param int    $position
     * @param string $description
     */
    public function __construct(Source $source, int $position, string $description)
    {
        parent::__construct(
            sprintf('Syntax Error: %s', $description),
            null,
            $source,
            [$position]
        );
    }
}
