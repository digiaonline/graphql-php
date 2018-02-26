<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Type\Contract\SchemaInterface;
use Digia\GraphQL\Type\Definition\Contract\OutputTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;

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
     * @var ResponsePath
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
     *
     * @param string                  $fieldName
     * @param FieldNode[]             $fieldNodes
     * @param OutputTypeInterface     $returnType
     * @param ObjectType              $parentType
     * @param ResponsePath            $path
     * @param SchemaInterface         $schema
     * @param array                   $fragments
     * @param mixed                   $rootValue
     * @param OperationDefinitionNode $operation
     * @param array                   $variableValues
     */
    public function __construct(
        string $fieldName,
        array $fieldNodes,
        OutputTypeInterface $returnType,
        ObjectType $parentType,
        ResponsePath $path,
        SchemaInterface $schema,
        array $fragments,
        mixed $rootValue,
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
     * @return ResponsePath
     */
    public function getPath(): ResponsePath
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
