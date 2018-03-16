<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;
use Digia\GraphQL\Language\Node\ObjectTypeDefinitionNode;
use function Digia\GraphQL\Type\resolveThunk;
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
class ObjectType extends ConfigObject implements TypeInterface, NamedTypeInterface, CompositeTypeInterface,
    OutputTypeInterface, NodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use FieldsTrait;
    use ResolveTrait;
    use NodeTrait;
    use ExtensionASTNodesTrait;

    /**
     * @var callable
     */
    private $isTypeOfFunction;

    /**
     * @var array|callable
     */
    private $_interfacesThunk;

    /**
     * @var InterfaceType[]
     */
    private $_interfaces = [];

    /**
     * @var bool
     */
    private $_isInterfacesDefined = false;

    /**
     * @inheritdoc
     */
    protected function afterConfig(): void
    {
        invariant(
            $this->getName() !== null,
            'Must provide name.'
        );

        if ($this->getIsTypeOf() !== null) {
            invariant(
                \is_callable($this->getIsTypeOf()),
                sprintf('%s must provide "isTypeOf" as a function.', $this->getName())
            );
        }
    }

    /**
     * @param mixed $value
     * @param mixed context
     * @param mixed $info
     * @return bool
     */
    public function isTypeOf($value, $context, $info): bool
    {
        return null !== $this->isTypeOfFunction
            ? \call_user_func($this->isTypeOfFunction, $value, $context, $info)
            : false;
    }

    /**
     * @return InterfaceType[]
     * @throws InvariantException
     */
    public function getInterfaces(): array
    {
        $this->defineInterfacesIfNecessary();

        return $this->_interfaces;
    }

    /**
     * @return null|callable
     */
    public function getIsTypeOf(): ?callable
    {
        return $this->isTypeOfFunction;
    }

    /**
     * @param array|callable $interfacesThunk
     * @return $this
     */
    protected function setInterfaces($interfacesThunk)
    {
        $this->_interfacesThunk = $interfacesThunk;

        return $this;
    }

    /**
     * @param null|callable $isTypeOfFunction
     * @return $this
     */
    protected function setIsTypeOf(?callable $isTypeOfFunction)
    {
        $this->isTypeOfFunction = $isTypeOfFunction;

        return $this;
    }

    /**
     * @throws InvariantException
     */
    protected function defineInterfacesIfNecessary()
    {
        // Interfaces are built lazily to avoid concurrency issues.
        if (!$this->_isInterfacesDefined) {
            $this->_interfaces = array_merge($this->defineInterfaces($this->_interfacesThunk), $this->_interfaces);

            $this->_isInterfacesDefined = true;
        }
    }

    /**
     * @param array|callable $interfacesThunk
     * @return array
     * @throws InvariantException
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
