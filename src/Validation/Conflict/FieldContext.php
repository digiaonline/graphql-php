<?php

namespace Digia\GraphQL\Validation\Conflict;

use Digia\GraphQL\Language\Node\FieldNode;
use GraphQL\Contracts\TypeSystem\Type\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\Field;
use GraphQL\Contracts\TypeSystem\Type\NamedTypeInterface;

class FieldContext
{
    /**
     * @var NamedTypeInterface|null
     */
    protected $parentType;

    /**
     * @var FieldNode
     */
    protected $node;

    /**
     * @var Field|null
     */
    protected $definition;

    /**
     * FieldContext constructor.
     * @param NamedTypeInterface|null $parentType
     * @param FieldNode                   $node
     * @param Field|null                  $definition
     */
    public function __construct(
        ?NamedTypeInterface $parentType,
        FieldNode $node,
        ?Field $definition = null
    ) {
        $this->parentType = $parentType;
        $this->node       = $node;
        $this->definition = $definition;
    }

    /**
     * @return NamedTypeInterface|null
     */
    public function getParentType(): ?NamedTypeInterface
    {
        return $this->parentType;
    }

    /**
     * @return FieldNode
     */
    public function getNode(): FieldNode
    {
        return $this->node;
    }

    /**
     * @return Field|null
     */
    public function getDefinition(): ?Field
    {
        return $this->definition;
    }
}
