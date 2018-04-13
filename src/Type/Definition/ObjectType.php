<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;
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
 *     $AddressType = newObjectType([
 *       'name'   => 'Address',
 *       'fields' => [
 *         'street'    => ['type' => String()],
 *         'number'    => ['type' => LInt()],
 *         'formatted' => [
 *           'type'    => String(),
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
 *     $PersonType = newObjectType([
 *       'name' => 'Person',
 *       'fields' => function () {
 *         return [
 *           'name'       => ['type' => String()],
 *           'bestFriend' => ['type' => $PersonType],
 *         ];
 *       }
 *     ]);
 */
class ObjectType implements TypeInterface, NamedTypeInterface, CompositeTypeInterface, OutputTypeInterface,
    FieldsAwareInterface, ASTNodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use FieldsTrait;
    use ResolveTrait;
    use ASTNodeTrait;
    use ExtensionASTNodesTrait;

    /**
     * @var callable
     */
    protected $isTypeOfCallback;

    /**
     * Interfaces can be defined either as an array or as a thunk.
     * Using thunks allows for cross-referencing of interfaces.
     *
     * @var array|callable
     */
    protected $interfacesOrThunk;

    /**
     * A list of interface instances.
     *
     * @var InterfaceType[]|null
     */
    protected $interfaces;

    /**
     * ObjectType constructor.
     *
     * @param string                        $name
     * @param null|string                   $description
     * @param array|callable                $fieldsOrThunk
     * @param array|callable                $interfacesOrThunk
     * @param callable|null                 $isTypeOfCallback
     * @param ObjectTypeDefinitionNode|null $astNode
     * @param ObjectTypeExtensionNode[]     $extensionASTNodes
     * @throws InvariantException
     */
    public function __construct(
        string $name,
        ?string $description,
        $fieldsOrThunk,
        $interfacesOrThunk,
        ?callable $isTypeOfCallback,
        ?ObjectTypeDefinitionNode $astNode,
        array $extensionASTNodes
    ) {
        $this->name              = $name;
        $this->description       = $description;
        $this->rawFieldsOrThunk  = $fieldsOrThunk;
        $this->interfacesOrThunk = $interfacesOrThunk;
        $this->isTypeOfCallback  = $isTypeOfCallback;
        $this->astNode           = $astNode;
        $this->extensionAstNodes = $extensionASTNodes;

        invariant(null !== $this->name, 'Must provide name.');
    }

    /**
     * @param mixed $value
     * @param mixed context
     * @param mixed $info
     * @return bool|PromiseInterface
     */
    public function isTypeOf($value, $context, $info)
    {
        return isset($this->isTypeOfCallback)
            ? \call_user_func($this->isTypeOfCallback, $value, $context, $info)
            : false;
    }

    /**
     * @return InterfaceType[]
     * @throws InvariantException
     */
    public function getInterfaces(): array
    {
        if (!isset($this->interfaces)) {
            $this->interfaces = $this->buildInterfaces($this->interfacesOrThunk);
        }
        return $this->interfaces;
    }

    /**
     * @return null|callable
     */
    public function getIsTypeOf(): ?callable
    {
        return $this->isTypeOfCallback;
    }

    /**
     * @param array|callable $interfacesOrThunk
     * @return array
     * @throws InvariantException
     */
    protected function buildInterfaces($interfacesOrThunk): array
    {
        $interfaces = resolveThunk($interfacesOrThunk);

        invariant(
            \is_array($interfaces),
            \sprintf('%s interfaces must be an array or a function which returns an array.', $this->name)
        );

        return $interfaces;
    }
}
