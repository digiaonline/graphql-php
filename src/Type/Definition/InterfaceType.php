<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigAwareInterface;
use Digia\GraphQL\Config\ConfigAwareTrait;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;
use function Digia\GraphQL\Util\invariant;

/**
 * Interface Type Definition
 *
 * When a field can return one of a heterogeneous set of types, a Interface type
 * is used to describe what types are possible, what fields are in common across
 * all types, as well as a function to determine which type is actually used
 * when the field is resolved.
 *
 * Example:
 *     $EntityType = GraphQLInterfaceType([
 *       'name' => 'Entity',
 *       'fields' => [
 *         'name' => ['type' => GraphQLString()]
 *       ]
 *     ]);
 */
class InterfaceType implements NamedTypeInterface, AbstractTypeInterface, CompositeTypeInterface,
    OutputTypeInterface, ConfigAwareInterface, NodeAwareInterface
{
    use ConfigAwareTrait;
    use NameTrait;
    use DescriptionTrait;
    use FieldsTrait;
    use NodeTrait;
    use ExtensionASTNodesTrait;
    use ResolveTypeTrait;

    /**
     * @inheritdoc
     */
    protected function afterConfig(): void
    {
        invariant(null !== $this->getName(), 'Must provide name.');
    }
}
