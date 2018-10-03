<?php

namespace Digia\GraphQL\Error;

interface ErrorHandlerInterface
{
    /**
     * @param GraphQLException $exception
     */
    public function handleError(GraphQLException $exception);
}
