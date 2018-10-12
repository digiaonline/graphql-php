<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Validation\ValidationExceptionInterface;

class SchemaValidationException extends GraphQLException implements ValidationExceptionInterface
{
}
