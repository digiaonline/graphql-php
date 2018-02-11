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
use function Digia\GraphQL\Util\invariant;

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
    private $_serialize;

    /**
     * @var ?callable
     */
    private $_parseValue;

    /**
     * @var ?callable
     */
    private $_parseLiteral;

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function afterConfig(): void
    {
        invariant(
            is_callable($this->_serialize),
            sprintf(
                '%s must provide "serialize" function. If this custom Scalar ' .
                'is also used as an input type, ensure "parseValue" and "parseLiteral" ' .
                'functions are also provided.',
                $this->getName()
            )
        );

        if ($this->_parseValue !== null || $this->_parseLiteral !== null) {
            invariant(
                is_callable($this->_parseValue) && is_callable($this->_parseLiteral),
                sprintf('%s must provide both "parseValue" and "parseLiteral" functions.', $this->getName())
            );
        }
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return call_user_func($this->_serialize, ($value));
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function parseValue($value)
    {
        return $this->_parseValue !== null ? call_user_func($this->_parseValue, $value) : null;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function parseLiteral(NodeInterface $astNode)
    {
        return $this->_parseLiteral !== null ? call_user_func($this->_parseLiteral, $astNode) : null;
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
        $this->_serialize = $serialize;
        return $this;
    }

    /**
     * @param callable $parseValue
     * @return ScalarType
     */
    protected function setParseValue(callable $parseValue): ScalarType
    {
        $this->_parseValue = $parseValue;
        return $this;
    }

    /**
     * @param callable $parseLiteral
     * @return ScalarType
     */
    protected function setParseLiteral(callable $parseLiteral): ScalarType
    {
        $this->_parseLiteral = $parseLiteral;
        return $this;
    }
}
