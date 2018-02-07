<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\ObjectTypeDefinitionNode;
use function Digia\GraphQL\Util\instantiateIfNecessary;
use Digia\GraphQL\Util\Util;

/**
 * Object Type Definition
 * Almost all of the GraphQL types you define will be object types. Object types
 * have a name, but most importantly describe their fields.
 * Example:
 *     const AddressType = new GraphQLObjectType({
 *       name: 'Address',
 *       fields: {
 *         street: { type: GraphQLString },
 *         number: { type: GraphQLInt },
 *         formatted: {
 *           type: GraphQLString,
 *           resolve(obj) {
 *             return obj.number + ' ' + obj.street
 *           }
 *         }
 *       }
 *     });
 * When two types need to refer to each other, or a type needs to refer to
 * itself in a field, you can use a function expression (aka a closure or a
 * thunk) to supply the fields lazily.
 * Example:
 *     const PersonType = new GraphQLObjectType({
 *       name: 'Person',
 *       fields: () => ({
 *         name: { type: GraphQLString },
 *         bestFriend: { type: PersonType },
 *       })
 *     });
 */

/**
 * Class ObjectType
 *
 * @package Digia\GraphQL\Type\Definition
 * @property ObjectTypeDefinitionNode $astNode
 */
class ObjectType implements TypeInterface, CompositeTypeInterface, NamedTypeInterface, OutputTypeInterface
{

    use NameTrait;
    use DescriptionTrait;
    use FieldsTrait;
    use ResolveTrait;
    use NodeTrait;
    use ExtensionASTNodesTrait;
    use ConfigTrait;

    /**
     * @var InterfaceType[]
     */
    private $interfaces = [];

    /**
     * @var callable
     */
    private $isTypeOf;

    /**
     * @return InterfaceType[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @param InterfaceType $interface
     * @return $this
     */
    protected function addInterface(InterfaceType $interface)
    {
        $this->interfaces[] = $interface;

        return $this;
    }

    /**
     * @param array $interfaces
     * @return $this
     */
    protected function setInterfaces(array $interfaces)
    {
        foreach ($interfaces as $config) {
            $this->addInterface(new InterfaceType($config));
        }

        return $this;
    }

    /**
     * @param callable $isTypeOf
     * @return $this
     */
    protected function setIsTypeOf(callable $isTypeOf)
    {
        $this->isTypeOf = $isTypeOf;

        return $this;
    }
}
