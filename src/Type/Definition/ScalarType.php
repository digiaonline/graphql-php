<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigAwareInterface;
use Digia\GraphQL\Config\ConfigAwareTrait;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeTrait;
use function Digia\GraphQL\Util\invariant;

class ScalarType implements TypeInterface, NamedTypeInterface, LeafTypeInterface, InputTypeInterface,
    OutputTypeInterface, ConfigAwareInterface, NodeAwareInterface
{
    use ConfigAwareTrait;
    use NameTrait;
    use DescriptionTrait;
    use NodeTrait;

    /**
     * @var callable
     */
    protected $serializeFunction;

    /**
     * @var callable|null
     */
    protected $parseValueFunction;

    /**
     * @var callable|null
     */
    protected $parseLiteralFunction;

    /**
     * @inheritdoc
     */
    protected function afterConfig(): void
    {
        invariant(
            \is_callable($this->serializeFunction),
            \sprintf(
                '%s must provide "serialize" function. If this custom Scalar ' .
                'is also used as an input type, ensure "parseValue" and "parseLiteral" ' .
                'functions are also provided.',
                $this->getName()
            )
        );

        if (null !== $this->parseValueFunction || null !== $this->parseLiteralFunction) {
            invariant(
                \is_callable($this->parseValueFunction) && \is_callable($this->parseLiteralFunction),
                \sprintf('%s must provide both "parseValue" and "parseLiteral" functions.', $this->getName())
            );
        }
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return \call_user_func($this->serializeFunction, $value);
    }

    /**
     * @param mixed $value
     * @return mixed|null
     */
    public function parseValue($value)
    {
        return null !== $this->parseValueFunction
            ? \call_user_func($this->parseValueFunction, $value)
            : null;
    }

    /**
     * @param NodeInterface $node
     * @param array|null    $variables
     * @return mixed|null
     */
    public function parseLiteral(NodeInterface $node, ?array $variables = null)
    {
        return null !== $this->parseLiteralFunction
            ? \call_user_func($this->parseLiteralFunction, $node, $variables)
            : null;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValidValue($value): bool
    {
        return null !== $this->parseValue($value);
    }

    /**
     * @param NodeInterface $node
     * @return bool
     */
    public function isValidLiteral(NodeInterface $node): bool
    {
        return null !== $this->parseLiteral($node);
    }

    /**
     * @param callable $serializeFunction
     * @return ScalarType
     */
    protected function setSerialize(callable $serializeFunction): ScalarType
    {
        $this->serializeFunction = $serializeFunction;
        return $this;
    }

    /**
     * @param callable $parseValueFunction
     * @return ScalarType
     */
    protected function setParseValue(callable $parseValueFunction): ScalarType
    {
        $this->parseValueFunction = $parseValueFunction;
        return $this;
    }

    /**
     * @param callable $parseLiteralFunction
     * @return ScalarType
     */
    protected function setParseLiteral(callable $parseLiteralFunction): ScalarType
    {
        $this->parseLiteralFunction = $parseLiteralFunction;
        return $this;
    }
}
