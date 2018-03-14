<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\EnumType;
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
use function Digia\GraphQL\Util\toString;

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
    if (!is_array($value)) {
        return false;
    }
    if (empty($value)) {
        return true;
    }
    $keys = array_keys($value);
    return $keys !== array_keys($keys);
}

/**
 * @param $resolver
 * @return bool
 */
function isValidResolver($resolver): bool
{
    return $resolver === null || \is_callable($resolver);
}

/**
 * @param $type
 * @throws InvariantException
 */
function assertType($type)
{
    invariant(
        $type instanceof TypeInterface,
        sprintf(sprintf('Expected %s to be a GraphQL type.', $type), toString($type))
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
        sprintf('Expected %s to be a GraphQL Scalar type.', $type)
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
        sprintf('Expected %s to be a GraphQL Object type.', $type)
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
        sprintf('Expected %s to be a GraphQL Interface type.', $type)
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
        sprintf('Expected %s to be a GraphQL Union type.', $type)
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
        sprintf('Expected %s to be a GraphQL Enum type.', $type)
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
        sprintf('Expected %s to be a GraphQL InputObject type.', $type)
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
        sprintf('Expected %s to be a GraphQL List type.', $type)
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
        sprintf('Expected %s to be a GraphQL NonNull type.', $type)
    );
}

/**
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
        sprintf('Expected %s to be a GraphQL input type.', $type)
    );
}

/**
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
        sprintf('Expected %s to be a GraphQL output type.', $type)
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
        sprintf('Expected %s to be a GraphQL leaf type.', $type)
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
        sprintf('Expected %s to be a GraphQL composite type.', $type)
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
        sprintf('Expected %s to be a GraphQL abstract type.', $type)
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
        sprintf('Expected %s to be a GraphQL wrapping type.', $type)
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
        sprintf('Expected %s to be a GraphQL nullable type.', $type)
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
        sprintf('Expected %s to be a GraphQL named type.', $type)
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
 */
function GraphQLScalarType(array $config = []): ScalarType
{
    return new ScalarType($config);
}

/**
 * @param array $config
 * @return EnumType
 */
function GraphQLEnumType(array $config = []): EnumType
{
    return new EnumType($config);
}

/**
 * @param array $config
 * @return InputObjectType
 */
function GraphQLInputObjectType(array $config = []): InputObjectType
{
    return new InputObjectType($config);
}

/**
 * @param array $config
 * @return InterfaceType
 */
function GraphQLInterfaceType(array $config = []): InterfaceType
{
    return new InterfaceType($config);
}

/**
 * @param array $config
 * @return ObjectType
 */
function GraphQLObjectType(array $config = []): ObjectType
{
    return new ObjectType($config);
}

/**
 * @param array $config
 * @return UnionType
 */
function GraphQLUnionType(array $config = []): UnionType
{
    return new UnionType($config);
}

/**
 * @param array $config
 * @return Schema
 */
function GraphQLSchema(array $config = []): Schema
{
    return new Schema($config);
}

/**
 * @param array $config
 * @return Directive
 */
function GraphQLDirective(array $config = []): Directive
{
    return new Directive($config);
}

/**
 * @param TypeInterface $ofType
 * @return ListType
 */
function GraphQLList(TypeInterface $ofType): ListType
{
    return new ListType($ofType);
}

/**
 * @param TypeInterface $ofType
 * @return NonNullType
 * @throws InvalidTypeException
 */
function GraphQLNonNull(TypeInterface $ofType): NonNullType
{
    return new NonNullType($ofType);
}
