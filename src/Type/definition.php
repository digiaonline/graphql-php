<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InputField;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InputTypeInterface;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\LeafTypeInterface;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\OutputTypeInterface;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Type\Definition\WrappingTypeInterface;
use function Digia\GraphQL\Util\invariant;

/**
 * @param $thunk
 * @return null|array
 */
function resolveThunk($thunk): ?array
{
    return \is_callable($thunk) ? $thunk() : $thunk;
}

/**
 * @param mixed $value
 * @return bool
 */
function isAssocArray($value): bool
{
    if (!\is_array($value)) {
        return false;
    }
    if (empty($value)) {
        return true;
    }
    $keys = \array_keys($value);
    return $keys !== \array_keys($keys);
}

/**
 * @param $type
 * @throws InvariantException
 */
function assertType($type)
{
    invariant(
        $type instanceof TypeInterface,
        \sprintf('Expected %s to be a GraphQL type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertScalarType(TypeInterface $type)
{
    invariant(
        $type instanceof ScalarType,
        \sprintf('Expected %s to be a GraphQL Scalar type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertObjectType(TypeInterface $type)
{
    invariant(
        $type instanceof ObjectType,
        \sprintf('Expected %s to be a GraphQL Object type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertInterfaceType(TypeInterface $type)
{
    invariant(
        $type instanceof InterfaceType,
        \sprintf('Expected %s to be a GraphQL Interface type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertUnionType(TypeInterface $type)
{
    invariant(
        $type instanceof UnionType,
        \sprintf('Expected %s to be a GraphQL Union type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertEnumType(TypeInterface $type)
{
    invariant(
        $type instanceof EnumType,
        \sprintf('Expected %s to be a GraphQL Enum type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertInputObjectType(TypeInterface $type)
{
    invariant(
        $type instanceof InputObjectType,
        \sprintf('Expected %s to be a GraphQL InputObject type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertListType(TypeInterface $type)
{
    invariant(
        $type instanceof ListType,
        \sprintf('Expected %s to be a GraphQL List type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertNonNullType(TypeInterface $type)
{
    invariant(
        $type instanceof NonNullType,
        \sprintf('Expected %s to be a GraphQL NonNull type.', (string)$type)
    );
}

/**
 * Whether a type is an input type cannot be determined with `instanceof`
 * because lists and non-nulls can also be output types if the wrapped type is an output type.
 *
 * @param TypeInterface|null $type
 * @return bool
 */
function isInputType(?TypeInterface $type): bool
{
    return null !== $type && ($type instanceof InputTypeInterface || ($type instanceof WrappingTypeInterface && isInputType($type->getOfType())));
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertInputType(TypeInterface $type)
{
    invariant(
        isInputType($type),
        \sprintf('Expected %s to be a GraphQL input type.', (string)$type)
    );
}

/**
 * Whether a type is an output type cannot be determined with `instanceof`
 * because lists and non-nulls can also be output types if the wrapped type is an output type.
 *
 * @param TypeInterface|null $type
 * @return bool
 */
function isOutputType(?TypeInterface $type): bool
{
    return null !== $type && ($type instanceof OutputTypeInterface || ($type instanceof WrappingTypeInterface && isOutputType($type->getOfType())));
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertOutputType(TypeInterface $type)
{
    invariant(
        isOutputType($type),
        \sprintf('Expected %s to be a GraphQL output type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertLeafType(TypeInterface $type)
{
    invariant(
        $type instanceof LeafTypeInterface,
        \sprintf('Expected %s to be a GraphQL leaf type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertCompositeType(TypeInterface $type)
{
    invariant(
        $type instanceof CompositeTypeInterface,
        \sprintf('Expected %s to be a GraphQL composite type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertAbstractType(TypeInterface $type)
{
    invariant(
        $type instanceof AbstractTypeInterface,
        \sprintf('Expected %s to be a GraphQL abstract type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertWrappingType(TypeInterface $type)
{
    invariant(
        $type instanceof WrappingTypeInterface,
        \sprintf('Expected %s to be a GraphQL wrapping type.', (string)$type)
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isNullableType(TypeInterface $type): bool
{
    return !($type instanceof NonNullType);
}

/**
 * @param TypeInterface $type
 * @return TypeInterface
 * @throws InvariantException
 */
function assertNullableType(TypeInterface $type): TypeInterface
{
    invariant(
        isNullableType($type),
        \sprintf('Expected %s to be a GraphQL nullable type.', (string)$type)
    );

    return $type;
}

/**
 * @param TypeInterface|null $type
 * @return TypeInterface|null
 */
function getNullableType(?TypeInterface $type): ?TypeInterface
{
    if (null === $type) {
        return null;
    }

    return $type instanceof NonNullType ? $type->getOfType() : $type;
}

/**
 * @param TypeInterface $type
 * @throws InvariantException
 */
function assertNamedType(TypeInterface $type)
{
    invariant(
        $type instanceof NamedTypeInterface,
        \sprintf('Expected %s to be a GraphQL named type.', (string)$type)
    );
}

/**
 * @param TypeInterface|null $type
 * @return NamedTypeInterface|null
 */
function getNamedType(?TypeInterface $type): ?NamedTypeInterface
{
    if (!$type) {
        return null;
    }

    $unwrappedType = $type;

    while ($unwrappedType instanceof WrappingTypeInterface) {
        $unwrappedType = $unwrappedType->getOfType();
    }

    return $unwrappedType;
}

/**
 * @param array $config
 * @return ScalarType
 * @throws InvariantException
 */
function newScalarType(array $config = []): ScalarType
{
    return new ScalarType(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['serialize'] ?? null,
        $config['parseValue'] ?? null,
        $config['parseLiteral'] ?? null,
        $config['astNode'] ?? null
    );
}

/**
 * @param array $config
 * @return EnumType
 * @throws InvariantException
 */
function newEnumType(array $config = []): EnumType
{
    return new EnumType(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['values'] ?? [],
        $config['astNode'] ?? null
    );
}

/**
 * @param array $config
 * @return EnumValue
 */
function newEnumValue(array $config = []): EnumValue
{
    return new EnumValue(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['deprecationReason'] ?? null,
        $config['astNode'] ?? null,
        $config['value'] ?? null
    );
}

/**
 * @param array $config
 * @return InputObjectType
 * @throws InvariantException
 */
function newInputObjectType(array $config = []): InputObjectType
{
    return new InputObjectType(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['fields'] ?? [],
        $config['astNode'] ?? null
    );
}

/**
 * @param array $config
 * @return InputField
 */
function newInputField(array $config = []): InputField
{
    return new InputField(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['type'] ?? null,
        $config['defaultValue'] ?? null,
        $config['astNode'] ?? null
    );
}

/**
 * @param array $config
 * @return InterfaceType
 * @throws InvariantException
 */
function newInterfaceType(array $config = []): InterfaceType
{
    return new InterfaceType(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['fields'] ?? [],
        $config['resolveType'] ?? null,
        $config['astNode'] ?? null,
        $config['extensionASTNodes'] ?? []
    );
}

/**
 * @param array $config
 * @return ObjectType
 * @throws InvariantException
 */
function newObjectType(array $config = []): ObjectType
{
    return new ObjectType(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['fields'] ?? [],
        $config['interfaces'] ?? [],
        $config['isTypeOf'] ?? null,
        $config['astNode'] ?? null,
        $config['extensionASTNodes'] ?? []
    );
}

/**
 * @param array $config
 * @return Field
 * @throws InvariantException
 */
function newField(array $config = []): Field
{
    return new Field(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['type'] ?? null,
        $config['args'] ?? [],
        $config['resolve'] ?? null,
        $config['subscribe'] ?? null,
        $config['deprecationReason'] ?? null,
        $config['astNode'] ?? null,
        $config['typeName'] ?? ''
    );
}

function newArgument(array $config = []): Argument
{
    return new Argument(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['type'] ?? null,
        $config['defaultValue'] ?? null,
        $config['astNode'] ?? null
    );
}

/**
 * @param array $config
 * @return UnionType
 * @throws InvariantException
 */
function newUnionType(array $config = []): UnionType
{
    return new UnionType(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['types'] ?? [],
        $config['resolveType'] ?? null,
        $config['astNode'] ?? null
    );
}

/**
 * @param array $config
 * @return Schema
 * @throws InvariantException
 */
function newSchema(array $config = []): Schema
{
    return new Schema(
        $config['query'] ?? null,
        $config['mutation'] ?? null,
        $config['subscription'] ?? null,
        $config['types'] ?? [],
        $config['directives'] ?? [],
        $config['assumeValue'] ?? false,
        $config['astNode'] ?? null
    );
}

/**
 * @param array $config
 * @return Directive
 * @throws InvariantException
 */
function newDirective(array $config = []): Directive
{
    return new Directive(
        $config['name'] ?? null,
        $config['description'] ?? null,
        $config['locations'] ?? [],
        $config['args'] ?? [],
        $config['astNode'] ?? null,
        $config['typeName'] ?? ''
    );
}

/**
 * @param TypeInterface $ofType
 * @return ListType
 */
function newList(TypeInterface $ofType): ListType
{
    return new ListType($ofType);
}

/**
 * @param TypeInterface $ofType
 * @return NonNullType
 * @throws InvalidTypeException
 */
function newNonNull(TypeInterface $ofType): NonNullType
{
    return new NonNullType($ofType);
}
