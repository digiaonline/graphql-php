<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ScalarTypeDefinitionNode;
use function Digia\GraphQL\Util\invariant;

class ScalarType implements TypeInterface, NamedTypeInterface, LeafTypeInterface, InputTypeInterface,
    OutputTypeInterface, ASTNodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use ASTNodeTrait;

    /**
     * @var callable
     */
    protected $serializeCallback;

    /**
     * @var callable|null
     */
    protected $parseValueCallback;

    /**
     * @var callable|null
     */
    protected $parseLiteralCallback;

    /**
     * ScalarType constructor.
     *
     * @param string                        $name
     * @param null|string                   $description
     * @param callable|null                 $serializeCallback
     * @param callable|null                 $parseValueCallback
     * @param callable|null                 $parseLiteralCallback
     * @param ScalarTypeDefinitionNode|null $astNode
     * @throws InvariantException
     */
    public function __construct(
        string $name,
        ?string $description,
        ?callable $serializeCallback,
        ?callable $parseValueCallback,
        ?callable $parseLiteralCallback,
        ?ScalarTypeDefinitionNode $astNode
    ) {
        $this->name                 = $name;
        $this->description          = $description;
        $this->serializeCallback    = $serializeCallback;
        $this->parseValueCallback   = $parseValueCallback;
        $this->parseLiteralCallback = $parseLiteralCallback;
        $this->astNode              = $astNode;

        invariant(
            \is_callable($this->serializeCallback),
            \sprintf(
                '%s must provide "serialize" function. If this custom Scalar ' .
                'is also used as an input type, ensure "parseValue" and "parseLiteral" ' .
                'functions are also provided.',
                $this->name
            )
        );

        if (null !== $this->parseValueCallback || null !== $this->parseLiteralCallback) {
            invariant(
                \is_callable($this->parseValueCallback) && \is_callable($this->parseLiteralCallback),
                \sprintf('%s must provide both "parseValue" and "parseLiteral" functions.', $this->name)
            );
        }
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return \call_user_func($this->serializeCallback, $value);
    }

    /**
     * @param mixed $value
     * @return mixed|null
     */
    public function parseValue($value)
    {
        return null !== $this->parseValueCallback
            ? \call_user_func($this->parseValueCallback, $value)
            : null;
    }

    /**
     * @param NodeInterface $node
     * @param array|null    $variables
     * @return mixed|null
     */
    public function parseLiteral(NodeInterface $node, ?array $variables = null)
    {
        return null !== $this->parseLiteralCallback
            ? \call_user_func($this->parseLiteralCallback, $node, $variables)
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
        $this->serializeCallback = $serializeFunction;
        return $this;
    }

    /**
     * @param callable $parseValueFunction
     * @return ScalarType
     */
    protected function setParseValue(callable $parseValueFunction): ScalarType
    {
        $this->parseValueCallback = $parseValueFunction;
        return $this;
    }

    /**
     * @param callable $parseLiteralFunction
     * @return ScalarType
     */
    protected function setParseLiteral(callable $parseLiteralFunction): ScalarType
    {
        $this->parseLiteralCallback = $parseLiteralFunction;
        return $this;
    }
}
