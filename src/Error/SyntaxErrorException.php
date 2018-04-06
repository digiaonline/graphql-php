<?php

namespace Digia\GraphQL\Error;

use Digia\GraphQL\Language\Source;

class SyntaxErrorException extends GraphQLException
{

    /**
     * SyntaxErrorException constructor.
     *
     * @param Source $source
     * @param int $position
     * @param string $description
     */
    public function __construct(
        Source $source,
        int $position,
        string $description
    ) {
        parent::__construct(
            sprintf('Syntax Error: %s', $description),
            null,
            $source,
            [$position]
        );
    }
}
