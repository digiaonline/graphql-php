<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\ASTNodeInterface;

use Digia\GraphQL\Language\AST\ASTNodeTrait;
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
    use ASTNodeTrait;
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
     */
    protected function addInterface(InterfaceType $interface): void
    {
        $this->interfaces[] = $interface;
    }

    /**
     * @param array $interfaces
     */
    protected function setInterfaces(array $interfaces): void
    {
        array_map(function ($interface) {
            $this->addInterface(instantiateIfNecessary(InterfaceType::class, $interface));
        }, $interfaces);
    }

    /**
     * @param callable $isTypeOf
     */
    protected function setIsTypeOf(callable $isTypeOf): void
    {
        $this->isTypeOf = $isTypeOf;
    }
}
