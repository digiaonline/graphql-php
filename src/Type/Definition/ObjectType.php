<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Type\Definition\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Type\Definition\Behavior\ExtensionASTNodesTrait;
use Digia\GraphQL\Type\Definition\Behavior\FieldsTrait;
use Digia\GraphQL\Type\Definition\Behavior\NameTrait;
use Digia\GraphQL\Type\Definition\Behavior\ResolveTrait;
use Digia\GraphQL\Type\Definition\Contract\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\OutputTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;
use function Digia\GraphQL\Util\instantiateFromArray;

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
     * @throws \Exception
     */
    protected function afterConfig(): void
    {
        // TODO: Uncomment when it is time.

//        invariant(
//            $this->getName() !== null,
//            'Must provide name.'
//        );
//
//        invariant(
//            $this->getIsTypeOf() !== null,
//            sprintf('%s must provide "isTypeOf" as a function.', $this->getName())
//        );
    }

    /**
     * @param mixed $value
     * @param mixed context
     * @param $info
     * @return bool
     */
    public function isTypeOf($value, $context, $info): bool
    {
        return $this->getIsTypeOf()($value, $context, $info);
    }

    /**
     * @return InterfaceType[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @return null|callable
     */
    public function getIsTypeOf(): ?callable
    {
        return $this->isTypeOf;
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
    protected function addInterfaces(array $interfaces)
    {
        foreach ($interfaces as $interface) {
            $this->addInterface($interface);
        }

        return $this;
    }

    /**
     * @param array $interfaces
     * @return $this
     */
    protected function setInterfaces(array $interfaces)
    {
        $this->addInterfaces(instantiateFromArray(InterfaceType::class, $interfaces));

        return $this;
    }

    /**
     * @param null|callable $isTypeOf
     * @return $this
     */
    protected function setIsTypeOf(?callable $isTypeOf)
    {
        $this->isTypeOf = $isTypeOf;

        return $this;
    }
}
