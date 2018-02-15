<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\ConfigObject;
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
class ObjectType extends ConfigObject implements TypeInterface, CompositeTypeInterface, NamedTypeInterface, OutputTypeInterface
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
    private $isTypeOf;

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
        $this->defineInterfacesIfNecessary();

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
    protected function defineInterfacesIfNecessary()
    {
        // Interfaces are built lazily to avoid concurrency issues.
        if (!$this->_isInterfacesDefined) {
            $this->_interfaces = array_merge($this->defineInterfaces($this->_interfacesThunk), $this->_interfaces);

            $this->_isInterfacesDefined = true;
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
