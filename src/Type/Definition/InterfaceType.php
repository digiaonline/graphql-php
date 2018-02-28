<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\ConfigObject;
use Digia\GraphQL\Language\AST\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NodeTrait;

/**
 * Interface Type Definition
 * When a field can return one of a heterogeneous set of types, a Interface type
 * is used to describe what types are possible, what fields are in common across
 * all types, as well as a function to determine which type is actually used
 * when the field is resolved.
 * Example:
 *     const EntityType = new GraphQLInterfaceType({
 *       name: 'Entity',
 *       fields: {
 *         name: { type: GraphQLString }
 *       }
 *     });
 */

/**
 * Class InterfaceType
 *
 * @package Digia\GraphQL\Type\Definition
 * @property InterfaceTypeDefinitionNode $astNode
 */
class InterfaceType extends ConfigObject implements AbstractTypeInterface, CompositeTypeInterface, NamedTypeInterface, OutputTypeInterface
{

    use NameTrait;
    use DescriptionTrait;
    use FieldsTrait;
    use NodeTrait;
    use ExtensionASTNodesTrait;
    use ResolveTypeTrait;

    /**
     * @inheritdoc
     */
    protected function beforeConfig(): void
    {
        $this->setName(TypeNameEnum::INTERFACE);
    }
}
