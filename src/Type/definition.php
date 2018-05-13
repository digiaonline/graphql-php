<?php

namespace Digia\GraphQL\Type;

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
use function Digia\GraphQL\Util\toString;

/**
 * @param callable|array|null $maybeThunk
 * @return null|array
 */
function resolveThunk($maybeThunk)
{
    return \is_callable($maybeThunk) ? $maybeThunk() : $maybeThunk;
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
 * @param mixed $type
 * @throws InvariantException
 */
function assertType($type)
{
    invariant(
        $type instanceof TypeInterface,
        \sprintf('Expected %s to be a GraphQL type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertScalarType($type)
{
    invariant(
        $type instanceof ScalarType,
        \sprintf('Expected %s to be a GraphQL Scalar type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertObjectType($type)
{
    invariant(
        $type instanceof ObjectType,
        \sprintf('Expected %s to be a GraphQL Object type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertInterfaceType($type)
{
    invariant(
        $type instanceof InterfaceType,
        \sprintf('Expected %s to be a GraphQL Interface type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertUnionType($type)
{
    invariant(
        $type instanceof UnionType,
        \sprintf('Expected %s to be a GraphQL Union type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertEnumType($type)
{
    invariant(
        $type instanceof EnumType,
        \sprintf('Expected %s to be a GraphQL Enum type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertInputObjectType($type)
{
    invariant(
        $type instanceof InputObjectType,
        \sprintf('Expected %s to be a GraphQL InputObject type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertListType($type)
{
    invariant(
        $type instanceof ListType,
        \sprintf('Expected %s to be a GraphQL List type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertNonNullType($type)
{
    invariant(
        $type instanceof NonNullType,
        \sprintf('Expected %s to be a GraphQL NonNull type.', toString($type))
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
    return null !== $type &&
        ($type instanceof InputTypeInterface ||
            ($type instanceof WrappingTypeInterface && isInputType($type->getOfType())));
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertInputType($type)
{
    invariant(
        isInputType($type),
        \sprintf('Expected %s to be a GraphQL input type.', toString($type))
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
    return null !== $type &&
        ($type instanceof OutputTypeInterface ||
            ($type instanceof WrappingTypeInterface && isOutputType($type->getOfType())));
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertOutputType($type)
{
    invariant(
        isOutputType($type),
        \sprintf('Expected %s to be a GraphQL output type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertLeafType($type)
{
    invariant(
        $type instanceof LeafTypeInterface,
        \sprintf('Expected %s to be a GraphQL leaf type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertCompositeType($type)
{
    invariant(
        $type instanceof CompositeTypeInterface,
        \sprintf('Expected %s to be a GraphQL composite type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertAbstractType($type)
{
    invariant(
        $type instanceof AbstractTypeInterface,
        \sprintf('Expected %s to be a GraphQL abstract type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertWrappingType($type)
{
    invariant(
        $type instanceof WrappingTypeInterface,
        \sprintf('Expected %s to be a GraphQL wrapping type.', toString($type))
    );
}

/**
 * @param mixed $type
 * @return bool
 */
function isNullableType($type): bool
{
    return !($type instanceof NonNullType);
}

/**
 * @param mixed $type
 * @return TypeInterface
 * @throws InvariantException
 */
function assertNullableType($type): TypeInterface
{
    invariant(
        isNullableType($type),
        \sprintf('Expected %s to be a GraphQL nullable type.', toString($type))
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
 * @param mixed $type
 * @throws InvariantException
 */
function assertNamedType($type)
{
    invariant(
        $type instanceof NamedTypeInterface,
        \sprintf('Expected %s to be a GraphQL named type.', toString($type))
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
 * Returns a new Scalar type after ensuring that its state is valid.
 *
 * @param array $config
 * @return ScalarType
 * @throws InvariantException
 */
function newScalarType(array $config = []): ScalarType
{
    invariant(isset($config['name']), 'Must provide name.');

    invariant(
        isset($config['serialize']) && \is_callable($config['serialize']),
        \sprintf(
            '%s must provide "serialize" function. If this custom Scalar ' .
            'is also used as an input type, ensure "parseValue" and "parseLiteral" ' .
            'functions are also provided.',
            $config['name']
        )
    );

    if (isset($config['parseValue']) || isset($config['parseLiteral'])) {
        invariant(
            (isset($config['parseValue']) && \is_callable($config['parseValue'])) &&
            (isset($config['parseLiteral']) && \is_callable($config['parseLiteral'])),
            \sprintf('%s must provide both "parseValue" and "parseLiteral" functions.', $config['name'])
        );
    }

    return new ScalarType(
        $config['name'],
        $config['description'] ?? null,
        $config['serialize'],
        $config['parseValue'] ?? null,
        $config['parseLiteral'] ?? null,
        $config['astNode'] ?? null
    );
}

/**
 * Returns a new Enum type after ensuring that its state is valid.
 *
 * @param array $config
 * @return EnumType
 * @throws InvariantException
 */
function newEnumType(array $config = []): EnumType
{
    invariant(isset($config['name']), 'Must provide name.');

    return new EnumType(
        $config['name'],
        $config['description'] ?? null,
        $config['values'] ?? [],
        $config['astNode'] ?? null
    );
}

/**
 * Returns a new Enum value after ensuring that its state is valid.
 *
 * @param array $config
 * @return EnumValue
 * @throws InvariantException
 */
function newEnumValue(array $config = []): EnumValue
{
    invariant(isset($config['name']), 'Must provide name.');

    return new EnumValue(
        $config['name'],
        $config['description'] ?? null,
        $config['deprecationReason'] ?? null,
        $config['astNode'] ?? null,
        $config['value'] ?? null
    );
}

/**
 * Returns a new Input Object type after ensuring that its state is valid.
 *
 * @param array $config
 * @return InputObjectType
 * @throws InvariantException
 */
function newInputObjectType(array $config = []): InputObjectType
{
    invariant(isset($config['name']), 'Must provide name.');

    return new InputObjectType(
        $config['name'],
        $config['description'] ?? null,
        $config['fields'] ?? [],
        $config['astNode'] ?? null
    );
}

/**
 * Returns a new Input field after ensuring that its state is valid.
 *
 * @param array $config
 * @return InputField
 * @throws InvariantException
 */
function newInputField(array $config = []): InputField
{
    invariant(isset($config['name']), 'Must provide name.');

    return new InputField(
        $config['name'],
        $config['description'] ?? null,
        $config['type'] ?? null,
        $config['defaultValue'] ?? null,
        $config['astNode'] ?? null
    );
}

/**
 * Returns a new Interface type after ensuring that its state is valid.
 *
 * @param array $config
 * @return InterfaceType
 * @throws InvariantException
 */
function newInterfaceType(array $config = []): InterfaceType
{
    invariant(isset($config['name']), 'Must provide name.');

    invariant(
        !isset($config['resolveType']) || null === $config['resolveType'] || \is_callable($config['resolveType']),
        \sprintf('%s must provide "resolveType" as a function.', $config['name'])
    );

    return new InterfaceType(
        $config['name'],
        $config['description'] ?? null,
        $config['fields'] ?? [],
        $config['resolveType'] ?? null,
        $config['astNode'] ?? null,
        $config['extensionASTNodes'] ?? []
    );
}

/**
 * Returns a new Object type after ensuring that its state is valid.
 *
 * @param array $config
 * @return ObjectType
 * @throws InvariantException
 */
function newObjectType(array $config = []): ObjectType
{
    invariant(isset($config['name']), 'Must provide name.');

    if (isset($config['isTypeOf'])) {
        invariant(
            \is_callable($config['isTypeOf']),
            \sprintf('%s must provide "isTypeOf" as a function.', $config['name'])
        );
    }

    return new ObjectType(
        $config['name'],
        $config['description'] ?? null,
        $config['fields'] ?? [],
        $config['interfaces'] ?? [],
        $config['isTypeOf'] ?? null,
        $config['astNode'] ?? null,
        $config['extensionASTNodes'] ?? []
    );
}

/**
 * Returns a new Field after ensuring that its state is valid.
 *
 * @param array $config
 * @return Field
 * @throws InvariantException
 */
function newField(array $config = []): Field
{
    invariant(isset($config['name']), 'Must provide name.');

    return new Field(
        $config['name'],
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

/**
 * Returns a new Argument after ensuring that its state is valid.
 *
 * @param array $config
 * @return Argument
 * @throws InvariantException
 */
function newArgument(array $config = []): Argument
{
    invariant(isset($config['name']), 'Must provide name.');

    return new Argument(
        $config['name'],
        $config['description'] ?? null,
        $config['type'] ?? null,
        $config['defaultValue'] ?? null,
        $config['astNode'] ?? null
    );
}

/**
 * Returns a new Union type after ensuring that its state is valid.
 *
 * @param array $config
 * @return UnionType
 * @throws InvariantException
 */
function newUnionType(array $config = []): UnionType
{
    invariant(isset($config['name']), 'Must provide name.');

    if (isset($config['resolveType'])) {
        invariant(
            \is_callable($config['resolveType']),
            \sprintf('%s must provide "resolveType" as a function.', $config['name'])
        );
    }

    return new UnionType(
        $config['name'],
        $config['description'] ?? null,
        $config['types'] ?? [],
        $config['resolveType'] ?? null,
        $config['astNode'] ?? null
    );
}

/**
 * Returns a new Schema after ensuring that its state is valid.
 *
 * @param array $config
 * @return Schema
 * @throws InvariantException
 */
function newSchema(array $config = []): Schema
{
    if (!isset($config['assumeValid']) || !$config['assumeValid']) {
        if (isset($config['types'])) {
            invariant(
                \is_array($config['types']),
                \sprintf('"types" must be Array if provided but got: %s.', toString($config['types']))
            );
        }

        if (isset($config['directives'])) {
            invariant(
                \is_array($config['directives']),
                \sprintf('"directives" must be Array if provided but got: %s.', toString($config['directives']))
            );
        }
    }

    return new Schema(
        $config['query'] ?? null,
        $config['mutation'] ?? null,
        $config['subscription'] ?? null,
        $config['types'] ?? [],
        $config['directives'] ?? [],
        $config['assumeValid'] ?? false,
        $config['astNode'] ?? null
    );
}

/**
 * Returns a new Directive after ensuring that its state is valid.
 *
 * @param array $config
 * @return Directive
 * @throws InvariantException
 */
function newDirective(array $config = []): Directive
{
    invariant(isset($config['name']), 'Must provide name.');

    invariant(
        isset($config['locations']) && \is_array($config['locations']),
        'Must provide locations for directive.'
    );

    return new Directive(
        $config['name'],
        $config['description'] ?? null,
        $config['locations'],
        $config['args'] ?? [],
        $config['astNode'] ?? null,
        $config['typeName'] ?? ''
    );
}

/**
 * Returns a new List type after ensuring that its state is valid.
 *
 * @param mixed $ofType
 * @return ListType
 * @throws InvariantException
 */
function newList($ofType): ListType
{
    assertType($ofType);

    return new ListType($ofType);
}

/**
 * Returns a new Non-null type after ensuring that its state is valid.
 *
 * @param mixed $ofType
 * @return NonNullType
 * @throws InvariantException
 */
function newNonNull($ofType): NonNullType
{
    assertNullableType($ofType);

    return new NonNullType($ofType);
}
