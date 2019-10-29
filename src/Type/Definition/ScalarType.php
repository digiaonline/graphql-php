<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ScalarTypeDefinitionNode;
use Digia\GraphQL\Schema\Definition;
use GraphQL\Contracts\TypeSystem\Type\NamedTypeInterface;
use GraphQL\Contracts\TypeSystem\Type\OutputTypeInterface;

class ScalarType extends Definition implements
    NamedTypeInterface,
    LeafTypeInterface,
    InputTypeInterface,
    OutputTypeInterface,
    SerializableTypeInterface,
    ASTNodeAwareInterface,
    DescriptionAwareInterface
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
     * @param callable                      $serializeCallback
     * @param callable|null                 $parseValueCallback
     * @param callable|null                 $parseLiteralCallback
     * @param ScalarTypeDefinitionNode|null $astNode
     */
    public function __construct(
        string $name,
        ?string $description,
        callable $serializeCallback,
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
}
