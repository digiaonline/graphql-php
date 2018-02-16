<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\Type\Definition\Contract\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\InputTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\LeafTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\OutputTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;
use Digia\GraphQL\Type\Definition\Contract\WrappingTypeInterface;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\Contract\DirectiveInterface;
use Digia\GraphQL\Type\Schema\Schema;
use function Digia\GraphQL\Util\invariant;
use function Digia\GraphQL\Util\toString;

/**
 * @param $thunk
 * @return null|array
 */
function resolveThunk($thunk): ?array
{
    return is_callable($thunk) ? $thunk() : $thunk;
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
    return $resolver === null || is_callable($resolver);
}

/**
 * @param $type
 * @throws \Exception
 */
function assertType($type)
{
    invariant(
        $type instanceof TypeInterface,
        sprintf(sprintf('Expected %s to be a GraphQL type.', toString($type)), toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertScalarType(TypeInterface $type)
{
    invariant(
        $type instanceof ScalarType,
        sprintf('Expected %s to be a GraphQL Scalar type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertObjectType(TypeInterface $type)
{
    invariant(
        $type instanceof ObjectType,
        sprintf('Expected %s to be a GraphQL Object type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertInterfaceType(TypeInterface $type)
{
    invariant(
        $type instanceof InterfaceType,
        sprintf('Expected %s to be a GraphQL Interface type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertUnionType(TypeInterface $type)
{
    invariant(
        $type instanceof UnionType,
        sprintf('Expected %s to be a GraphQL Union type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertEnumType(TypeInterface $type)
{
    invariant(
        $type instanceof EnumType,
        sprintf('Expected %s to be a GraphQL Enum type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertInputObjectType(TypeInterface $type)
{
    invariant(
        $type instanceof InputObjectType,
        sprintf('Expected %s to be a GraphQL InputObject type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertListType(TypeInterface $type)
{
    invariant(
        $type instanceof ListType,
        sprintf('Expected %s to be a GraphQL List type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertNonNullType(TypeInterface $type)
{
    invariant(
        $type instanceof NonNullType,
        sprintf('Expected %s to be a GraphQL NonNull type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isInputType(TypeInterface $type): bool
{
    return $type instanceof InputTypeInterface
        || ($type instanceof WrappingTypeInterface && isInputType($type->getOfType()));
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertInputType(TypeInterface $type)
{
    invariant(
        isInputType($type),
        sprintf('Expected %s to be a GraphQL input type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isOutputType(TypeInterface $type): bool
{
    return $type instanceof OutputTypeInterface
        || ($type instanceof WrappingTypeInterface && isOutputType($type->getOfType()));
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertOutputType(TypeInterface $type)
{
    invariant(
        isOutputType($type),
        sprintf('Expected %s to be a GraphQL output type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertLeafType(TypeInterface $type)
{
    invariant(
        $type instanceof LeafTypeInterface,
        sprintf('Expected %s to be a GraphQL leaf type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertCompositeType(TypeInterface $type)
{
    invariant(
        $type instanceof CompositeTypeInterface,
        sprintf('Expected %s to be a GraphQL composite type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertAbstractType(TypeInterface $type)
{
    invariant(
        $type instanceof AbstractTypeInterface,
        sprintf('Expected %s to be a GraphQL abstract type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertWrappingType(TypeInterface $type)
{
    invariant(
        $type instanceof WrappingTypeInterface,
        sprintf('Expected %s to be a GraphQL wrapping type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isNullableType(TypeInterface $type): bool
{
    return !$type instanceof NonNullType;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertNullableType(TypeInterface $type)
{
    invariant(
        isNullableType($type),
        sprintf('Expected %s to be a GraphQL nullable type.', toString($type))
    );
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertNamedType(TypeInterface $type)
{
    invariant(
        $type instanceof NamedTypeInterface,
        sprintf('Expected %s to be a GraphQL named type.', toString($type))
    );
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
 * @throws \TypeError
 */
function GraphQLList(TypeInterface $ofType): ListType
{
    return new ListType($ofType);
}

/**
 * @param TypeInterface $ofType
 * @return NonNullType
 * @throws \TypeError
 */
function GraphQLNonNull(TypeInterface $ofType): NonNullType
{
    return new NonNullType($ofType);
}
