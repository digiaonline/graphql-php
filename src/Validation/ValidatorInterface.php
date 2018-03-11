<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Visitor\VisitorBreak;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\TypeInfo;

interface ValidatorInterface
{
    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @param array           $rules
     * @param TypeInfo|null   $typeInfo
     * @return array|GraphQLException[]
     */
    public function validate(
        SchemaInterface $schema,
        DocumentNode $document,
        array $rules = [],
        ?TypeInfo $typeInfo = null
    ): array;
}
