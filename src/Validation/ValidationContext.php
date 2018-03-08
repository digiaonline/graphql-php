<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\TypeInfo;

class ValidationContext
{
    /**
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * @var DocumentNode
     */
    protected $documentNode;

    /**
     * @var TypeInfo
     */
    protected $typeInfo;

    /**
     * @var array|GraphQLError[]
     */
    protected $errors = [];

    /**
     * ValidationContext constructor.
     * @param SchemaInterface $schema
     * @param DocumentNode    $documentNode
     * @param TypeInfo        $typeInfo
     */
    public function __construct(SchemaInterface $schema, DocumentNode $documentNode, TypeInfo $typeInfo)
    {
        $this->schema       = $schema;
        $this->documentNode = $documentNode;
        $this->typeInfo     = $typeInfo;
    }

    /**
     * @param GraphQLError $error
     */
    public function reportError(GraphQLError $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return array|GraphQLError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return TypeInterface|null
     */
    public function getParentType(): ?TypeInterface
    {
        return $this->typeInfo->getParentType();
    }
}
