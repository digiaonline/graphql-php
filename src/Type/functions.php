<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\Type\Contract\AbstractTypeInterface;
use Digia\GraphQL\Type\Contract\CompositeTypeInterface;
use Digia\GraphQL\Type\Contract\InputTypeInterface;
use Digia\GraphQL\Type\Contract\LeafTypeInterface;
use Digia\GraphQL\Type\Contract\NamedTypeInterface;
use Digia\GraphQL\Type\Contract\OutputTypeInterface;
use Digia\GraphQL\Type\Contract\TypeInterface;
use Digia\GraphQL\Type\Contract\WrappingTypeInterface;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\Scalar\AbstractScalarType;
use Digia\GraphQL\Type\Definition\Scalar\BooleanType;
use Digia\GraphQL\Type\Definition\Scalar\FloatType;
use Digia\GraphQL\Type\Definition\Scalar\IDType;
use Digia\GraphQL\Type\Definition\Scalar\IntType;
use Digia\GraphQL\Type\Definition\Scalar\StringType;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Type\Directive\AbstractDirective;
use Digia\GraphQL\Type\Directive\DeprecatedDirective;
use Digia\GraphQL\Type\Directive\DirectiveInterface;
use Digia\GraphQL\Type\Directive\IncludeDirective;
use Digia\GraphQL\Type\Directive\SkipDirective;
use function Digia\GraphQL\Util\invariant;

/**
 * @param $object
 * @return bool
 */
function isPlainObj($object): bool
{
    return is_object($object);
}

/**
 * @param mixed $object
 * @return bool
 */
function isDirective($object): bool
{
    return $object instanceof AbstractDirective;
}

/**
 * @param $type
 * @return bool
 */
function isType($type): bool
{
    return $type instanceof TypeInterface;
}

/**
 * @param $type
 * @throws \Exception
 */
function assertType($type)
{
    invariant(
        isType($type),
        "Expected {$type} to be a GraphQL Scalar type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isScalarType(TypeInterface $type): bool
{
    return $type instanceof AbstractScalarType;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertScalarType(TypeInterface $type)
{
    invariant(
        isScalarType($type),
        "Expected {$type} to be a GraphQL Scalar type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isObjectType(TypeInterface $type): bool
{
    return $type instanceof ObjectType;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertObjectType(TypeInterface $type)
{
    invariant(
        isObjectType($type),
        "Expected {$type} to be a GraphQL Object type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isInterfaceType(TypeInterface $type): bool
{
    return $type instanceof InputTypeInterface;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertInterfaceType(TypeInterface $type)
{
    invariant(
        isInterfaceType($type),
        "Expected {$type} to be a GraphQL Interface type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isUnionType(TypeInterface $type): bool
{
    return $type instanceof UnionType;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertUnionType(TypeInterface $type)
{
    invariant(
        isUnionType($type),
        "Expected {$type} to be a GraphQL Union type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isEnumType(TypeInterface $type): bool
{
    return $type instanceof EnumType;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertEnumType(TypeInterface $type)
{
    invariant(
        isEnumType($type),
        "Expected {$type} to be a GraphQL Enum type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isInputObjectType(TypeInterface $type): bool
{
    return $type instanceof InputObjectType;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertInputObjectType(TypeInterface $type)
{
    invariant(
        isInputObjectType($type),
        "Expected {$type} to be a GraphQL InputObject type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isListType(TypeInterface $type): bool
{
    return $type instanceof ListType;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertListType(TypeInterface $type)
{
    invariant(
        isListType($type),
        "Expected {$type} to be a GraphQL List type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isNonNullType(TypeInterface $type): bool
{
    return $type instanceof NonNullType;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertNonNullType(TypeInterface $type)
{
    invariant(
        isNonNullType($type),
        "Expected {$type} to be a GraphQL NonNull type."
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
        "Expected {$type} to be a GraphQL input type."
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
        "Expected {$type} to be a GraphQL output type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isLeafType(TypeInterface $type): bool
{
    return $type instanceof LeafTypeInterface;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertLeafType(TypeInterface $type)
{
    invariant(
        isLeafType($type),
        "Expected {$type} to be a GraphQL leaf type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isCompositeType(TypeInterface $type): bool
{
    return $type instanceof CompositeTypeInterface;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertCompositeType(TypeInterface $type)
{
    invariant(
        isCompositeType($type),
        "Expected {$type} to be a GraphQL composite type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isAbstractType(TypeInterface $type): bool
{
    return $type instanceof AbstractTypeInterface;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertAbstractType(TypeInterface $type)
{
    invariant(
        isAbstractType($type),
        "Expected {$type} to be a GraphQL abstract type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isWrappingType(TypeInterface $type): bool
{
    return $type instanceof WrappingTypeInterface;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertWrappingType(TypeInterface $type)
{
    invariant(
        isWrappingType($type),
        "Expected {$type} to be a GraphQL wrapping type."
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
        "Expected {$type} to be a GraphQL nullable type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isNamedType(TypeInterface $type): bool
{
    return $type instanceof NamedTypeInterface;
}

/**
 * @param TypeInterface $type
 * @throws \Exception
 */
function assertNamedType(TypeInterface $type)
{
    invariant(
        isNamedType($type),
        "Expected {$type} to be a GraphQL named type."
    );
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isSpecifiedScalarType(TypeInterface $type): bool
{
    return \in_array(get_class($type), [
        StringType::class,
        IntType::class,
        FloatType::class,
        BooleanType::class,
        IDType::class,
    ], true);
}

/**
 * @param DirectiveInterface $directive
 * @return bool
 */
function isSpecifiedDirective(DirectiveInterface $directive): bool
{
    return \in_array(get_class($directive), [
        IncludeDirective::class,
        SkipDirective::class,
        DeprecatedDirective::class,
    ], true);
}
