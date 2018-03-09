<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\ScalarTypeDefinitionNode;
use function Digia\GraphQL\Util\invariant;

/**
 * Class ScalarType
 *
 * @package Digia\GraphQL\Type\Definition
 * @property ScalarTypeDefinitionNode $astNode
 * @codeCoverageIgnore
 */
class ScalarType extends ConfigObject implements TypeInterface, NamedTypeInterface, LeafTypeInterface, InputTypeInterface, OutputTypeInterface
{

    use NameTrait;
    use DescriptionTrait;
    use NodeTrait;

    /**
     * @var callable
     */
    private $_serializeFunction;

    /**
     * @var ?callable
     */
    private $_parseValueFunction;

    /**
     * @var ?callable
     */
    private $_parseLiteralFunction;

    /**
     * @inheritdoc
     */
    protected function afterConfig(): void
    {
        invariant(
            \is_callable($this->_serializeFunction),
            sprintf(
                '%s must provide "serialize" function. If this custom Scalar ' .
                'is also used as an input type, ensure "parseValue" and "parseLiteral" ' .
                'functions are also provided.',
                $this->getName()
            )
        );

        if ($this->_parseValueFunction !== null || $this->_parseLiteralFunction !== null) {
            invariant(
                \is_callable($this->_parseValueFunction) && \is_callable($this->_parseLiteralFunction),
                sprintf('%s must provide both "parseValue" and "parseLiteral" functions.', $this->getName())
            );
        }
    }

    /**
     * @param array ...$args
     * @return mixed
     */
    public function serialize(...$args)
    {
        return \call_user_func_array($this->_serializeFunction, $args);
    }

    /**
     * @param array ...$args
     * @return mixed|null
     */
    public function parseValue(...$args)
    {
        return $this->_parseValueFunction !== null ? \call_user_func_array($this->_parseValueFunction, $args) : null;
    }

    /**
     * @param array ...$args
     * @return mixed|null
     */
    public function parseLiteral(...$args)
    {
        return $this->_parseLiteralFunction !== null ? \call_user_func_array($this->_parseLiteralFunction,
            $args) : null;
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
     * @param callable $serializeFunction
     * @return ScalarType
     */
    protected function setSerialize(callable $serializeFunction): ScalarType
    {
        $this->_serializeFunction = $serializeFunction;
        return $this;
    }

    /**
     * @param callable $parseValueFunction
     * @return ScalarType
     */
    protected function setParseValue(callable $parseValueFunction): ScalarType
    {
        $this->_parseValueFunction = $parseValueFunction;
        return $this;
    }

    /**
     * @param callable $parseLiteralFunction
     * @return ScalarType
     */
    protected function setParseLiteral(callable $parseLiteralFunction): ScalarType
    {
        $this->_parseLiteralFunction = $parseLiteralFunction;
        return $this;
    }
}
