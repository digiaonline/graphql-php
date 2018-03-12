<?php

namespace Digia\GraphQL\Validation\Conflict;

use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\Field;

class FieldContext
{
    /**
     * @var CompositeTypeInterface|null
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
     * @param CompositeTypeInterface|null $parentType
     * @param FieldNode                   $node
     * @param Field|null                  $definition
     */
    public function __construct(
        ?CompositeTypeInterface $parentType,
        FieldNode $node,
        ?Field $definition = null
    ) {
        $this->parentType = $parentType;
        $this->node       = $node;
        $this->definition = $definition;
    }

    /**
     * @return CompositeTypeInterface|null
     */
    public function getParentType(): ?CompositeTypeInterface
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
