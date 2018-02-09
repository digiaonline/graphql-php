<?php

namespace Digia\GraphQL\Type\Definition\Scalar;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Type\Definition\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\ScalarTypeDefinitionNode;
use Digia\GraphQL\Type\Definition\Behavior\NameTrait;
use Digia\GraphQL\Type\Definition\Contract\InputTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\LeafTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\OutputTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;

/**
 * Class ScalarType
 *
 * @package Digia\GraphQL\Type\Definition
 * @property ScalarTypeDefinitionNode $astNode
 * @codeCoverageIgnore
 */
class ScalarType implements TypeInterface, LeafTypeInterface, NamedTypeInterface, InputTypeInterface, OutputTypeInterface
{

    use NameTrait;
    use DescriptionTrait;
    use NodeTrait;
    use ConfigTrait;

    /**
     * @var callable
     */
    private $serialize;

    /**
     * @var callable
     */
    private $parseValue;

    /**
     * @var callable
     */
    private $parseLiteral;

    /**
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return $this->serialize !== null ? $this->{$this->serialize}($value) : null;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function parseValue($value)
    {
        return $this->parseValue !== null ? $this->{$this->parseValue}($value) : null;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function parseLiteral(NodeInterface $astNode)
    {
        return $this->parseLiteral !== null ? $this->{$this->parseLiteral}($astNode) : null;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValidValue($value): bool
    {
        return $this->parseValue($value) !== null;
    }

    /**
     * @param NodeInterface $ast
     * @return bool
     */
    public function isValidLiteral($ast): bool
    {
        return $this->parseLiteral($ast) !== null;
    }

    /**
     * @param callable $serialize
     * @return ScalarType
     */
    protected function setSerialize(callable $serialize): ScalarType
    {
        $this->serialize = $serialize;
        return $this;
    }

    /**
     * @param callable $parseValue
     * @return ScalarType
     */
    protected function setParseValue(callable $parseValue): ScalarType
    {
        $this->parseValue = $parseValue;
        return $this;
    }

    /**
     * @param callable $parseLiteral
     * @return ScalarType
     */
    protected function setParseLiteral(callable $parseLiteral): ScalarType
    {
        $this->parseLiteral = $parseLiteral;
        return $this;
    }
}
