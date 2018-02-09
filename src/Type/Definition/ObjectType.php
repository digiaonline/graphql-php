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
use function Digia\GraphQL\Type\resolveThunk;
use function Digia\GraphQL\Util\instantiateFromArray;
use function Digia\GraphQL\Util\invariant;

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
     * @var callable
     */
    private $isTypeOf;

    /**
     * @var array|callable
     */
    private $_interfacesThunk;

    /**
     * @var InterfaceType[]
     */
    private $_interfaces;

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function afterConfig(): void
    {
        invariant(
            $this->getName() !== null,
            'Must provide name.'
        );

        if ($this->getIsTypeOf() !== null) {
            invariant(
                is_callable($this->getIsTypeOf()),
                sprintf('%s must provide "isTypeOf" as a function.', $this->getName())
            );
        }
    }

    /**
     * @param mixed $value
     * @param mixed context
     * @param       $info
     * @return bool
     */
    public function isTypeOf($value, $context, $info): bool
    {
        return $this->getIsTypeOf()($value, $context, $info);
    }

    /**
     * @return InterfaceType[]
     * @throws \Exception
     */
    public function getInterfaces(): array
    {
        $this->buildInterfaces();

        return $this->_interfaces;
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
     * @throws \Exception
     */
    protected function addInterface(InterfaceType $interface)
    {
        $this->buildInterfaces();

        $this->_interfaces[] = $interface;

        return $this;
    }

    /**
     * @param array $interfaces
     * @return $this
     * @throws \Exception
     */
    protected function addInterfaces(array $interfaces)
    {
        foreach ($interfaces as $interface) {
            $this->addInterface($interface);
        }

        return $this;
    }

    /**
     * @param array $interfacesThunk
     * @return $this
     */
    protected function setInterfaces(array $interfacesThunk)
    {
        $this->_interfacesThunk = $interfacesThunk;

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

    /**
     * @throws \Exception
     */
    protected function buildInterfaces()
    {
        if ($this->_interfaces === null) {
            $this->_interfaces = $this->defineInterfaces($this->_interfacesThunk);
        }
    }

    /**
     * @param ObjectType     $type
     * @param array|callable $interfacesThunk
     * @return array
     * @throws \Exception
     */
    protected function defineInterfaces($interfacesThunk): array
    {
        $interfaces = resolveThunk($interfacesThunk) ?: [];

        invariant(
            is_array($interfaces),
            sprintf('%s interfaces must be an array or a function which returns an array.', $this->getName())
        );

        return $interfaces;
    }
}
