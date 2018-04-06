<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigAwareInterface;
use Digia\GraphQL\Config\ConfigAwareTrait;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;
use React\Promise\PromiseInterface;
use function Digia\GraphQL\Type\resolveThunk;
use function Digia\GraphQL\Util\invariant;

/**
 * Object Type Definition
 *
 * Almost all of the GraphQL types you define will be object types. Object types
 * have a name, but most importantly describe their fields.
 *
 * Example:
 *
 *     $AddressType = GraphQLObjectType([
 *       'name'   => 'Address',
 *       'fields' => [
 *         'street'    => ['type' => GraphQLString()],
 *         'number'    => ['type' => GraphQLInt()],
 *         'formatted' => [
 *           'type'    => GraphQLString(),
 *           'resolve' => function ($obj) {
 *             return $obj->number . ' ' . $obj->street
 *           }
 *         ]
 *       ]
 *     ]);
 *
 * When two types need to refer to each other, or a type needs to refer to
 * itself in a field, you can use a function expression (aka a closure or a
 * thunk) to supply the fields lazily.
 *
 * Example:
 *
 *     $PersonType = GraphQLObjectType([
 *       'name' => 'Person',
 *       'fields' => function () {
 *         return [
 *           'name'       => ['type' => GraphQLString()],
 *           'bestFriend' => ['type' => $PersonType],
 *         ];
 *       }
 *     ]);
 */
class ObjectType implements TypeInterface, NamedTypeInterface, CompositeTypeInterface, OutputTypeInterface,
    ConfigAwareInterface, NodeAwareInterface
{

    use ConfigAwareTrait;
    use NameTrait;
    use DescriptionTrait;
    use FieldsTrait;
    use ResolveTrait;
    use NodeTrait;
    use ExtensionASTNodesTrait;

    /**
     * @var callable
     */
    protected $isTypeOfFunction;

    /**
     * @var array|callable
     */
    protected $interfacesOrThunk;

    /**
     * @var InterfaceType[]|null
     */
    protected $interfaces;

    /**
     * @param mixed $value
     * @param mixed context
     * @param mixed $info
     *
     * @return bool|PromiseInterface
     */
    public function isTypeOf($value, $context, $info)
    {
        return isset($this->isTypeOfFunction)
            ? \call_user_func($this->isTypeOfFunction, $value, $context, $info)
            : false;
    }

    /**
     * @return InterfaceType[]
     * @throws InvariantException
     */
    public function getInterfaces(): array
    {
        if (!isset($this->interfaces)) {
            $this->interfaces = $this->buildInterfaces($this->interfacesOrThunk ?? []);
        }

        return $this->interfaces;
    }

    /**
     * Objects are created using the `ConfigAwareTrait` constructor which will
     * automatically call this method when setting arguments from
     * `$config['interfaces']`.
     *
     * @param array|callable $interfacesOrThunk
     *
     * @return $this
     */
    protected function setInterfaces($interfacesOrThunk)
    {
        $this->interfacesOrThunk = $interfacesOrThunk;

        return $this;
    }

    /**
     * @param array|callable $interfacesOrThunk
     *
     * @return array
     * @throws InvariantException
     */
    protected function buildInterfaces($interfacesOrThunk): array
    {
        $interfaces = resolveThunk($interfacesOrThunk);

        invariant(
            \is_array($interfaces),
            \sprintf('%s interfaces must be an array or a function which returns an array.',
                $this->getName())
        );

        return $interfaces;
    }

    /**
     * @inheritdoc
     */
    protected function afterConfig(): void
    {
        invariant(null !== $this->getName(), 'Must provide name.');

        if ($this->getIsTypeOf() !== null) {
            invariant(
                \is_callable($this->getIsTypeOf()),
                \sprintf('%s must provide "isTypeOf" as a function.',
                    $this->getName())
            );
        }
    }

    /**
     * @return null|callable
     */
    public function getIsTypeOf(): ?callable
    {
        return $this->isTypeOfFunction;
    }

    /**
     * @param null|callable $isTypeOfFunction
     *
     * @return $this
     */
    protected function setIsTypeOf(?callable $isTypeOfFunction)
    {
        $this->isTypeOfFunction = $isTypeOfFunction;

        return $this;
    }
}
