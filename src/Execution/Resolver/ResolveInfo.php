<?php

namespace Digia\GraphQL\Execution\Resolver;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Execution\ResponsePath;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\OutputTypeInterface;
use Digia\GraphQL\Type\SchemaInterface;

class ResolveInfo extends ConfigObject
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
