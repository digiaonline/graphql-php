<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\OutputTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\SchemaInterface;

class ResolveInfo
{
    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var FieldNode[]
     */
    protected $fieldNodes;

    /**
     * @var OutputTypeInterface
     */
    protected $returnType;

    /**
     * @var ObjectType
     */
    protected $parentType;

    /**
     * @var array|null
     */
    protected $path;

    /**
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * @var array
     */
    protected $fragments;

    /**
     * @var mixed
     */
    protected $rootValue;

    /**
     * @var OperationDefinitionNode
     */
    protected $operation;

    /**
     * @var array
     */
    protected $variableValues;

    /**
     * ResolveInfo constructor.
     * @param string                  $fieldName
     * @param FieldNode[]             $fieldNodes
     * @param TypeInterface           $returnType
     * @param ObjectType              $parentType
     * @param array|null              $path
     * @param SchemaInterface         $schema
     * @param array                   $fragments
     * @param mixed                   $rootValue
     * @param OperationDefinitionNode $operation
     * @param array                   $variableValues
     */
    public function __construct(
        string $fieldName,
        ?array $fieldNodes,
        TypeInterface $returnType,
        ObjectType $parentType,
        ?array $path,
        SchemaInterface $schema,
        array $fragments,
        $rootValue,
        OperationDefinitionNode $operation,
        array $variableValues
    ) {
        $this->fieldName      = $fieldName;
        $this->fieldNodes     = $fieldNodes;
        $this->returnType     = $returnType;
        $this->parentType     = $parentType;
        $this->path           = $path;
        $this->schema         = $schema;
        $this->fragments      = $fragments;
        $this->rootValue      = $rootValue;
        $this->operation      = $operation;
        $this->variableValues = $variableValues;
    }


    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return FieldNode[]
     */
    public function getFieldNodes(): array
    {
        return $this->fieldNodes;
    }

    /**
     * @return OutputTypeInterface
     */
    public function getReturnType(): OutputTypeInterface
    {
        return $this->returnType;
    }

    /**
     * @return ObjectType
     */
    public function getParentType(): ObjectType
    {
        return $this->parentType;
    }

    /**
     * @return array
     */
    public function getPath(): ?array
    {
        return $this->path;
    }

    /**
     * @return SchemaInterface
     */
    public function getSchema(): SchemaInterface
    {
        return $this->schema;
    }

    /**
     * @return array
     */
    public function getFragments(): array
    {
        return $this->fragments;
    }

    /**
     * @return mixed
     */
    public function getRootValue()
    {
        return $this->rootValue;
    }

    /**
     * @return OperationDefinitionNode
     */
    public function getOperation(): OperationDefinitionNode
    {
        return $this->operation;
    }

    /**
     * @return array
     */
    public function getVariableValues(): array
    {
        return $this->variableValues;
    }
}
