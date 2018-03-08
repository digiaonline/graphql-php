<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return ObjectType
 */
function __Schema(): ObjectType
{
    return GraphQL::get(GraphQL::INTROSPECTION_SCHEMA);
}

/**
 * @return ObjectType
 */
function __Directive(): ObjectType
{
    return GraphQL::get(GraphQL::INTROSPECTION_DIRECTIVE);

}

/**
 * @return EnumType
 */
function __DirectiveLocation(): EnumType
{
    return GraphQL::get(GraphQL::INTROSPECTION_DIRECTIVE_LOCATION);

}

/**
 * @return ObjectType
 */
function __Type(): ObjectType
{
    return GraphQL::get(GraphQL::INTROSPECTION_TYPE);
}

/**
 * @return ObjectType
 */
function __Field(): ObjectType
{
    return GraphQL::get(GraphQL::INTROSPECTION_FIELD);
}

/**
 * @return ObjectType
 */
function __InputValue(): ObjectType
{
    return GraphQL::get(GraphQL::INTROSPECTION_INPUT_VALUE);
}

/**
 * @return ObjectType
 */
function __EnumValue(): ObjectType
{
    return GraphQL::get(GraphQL::INTROSPECTION_ENUM_VALUE);
}

function __TypeKind(): EnumType
{
    return GraphQL::get(GraphQL::INTROSPECTION_TYPE_KIND);
}

/**
 * @return Field
 * @throws \TypeError
 */
function SchemaMetaFieldDefinition(): Field
{
    return new Field([
        'name'        => '__schema',
        'type'        => GraphQLNonNull(__Schema()),
        'description' => 'Access the current type schema of this server.',
        'resolve'     => function ($source, $args, $context, $info): SchemaInterface {
            [$schema] = $info;
            return $schema;
        }
    ]);
}

/**
 * @return Field
 * @throws \TypeError
 */
function TypeMetaFieldDefinition(): Field
{
    return new Field([
        'name'        => '__type',
        'type'        => __Type(),
        'description' => 'Request the type information of a single type.',
        'args'        => [
            'name' => ['type' => GraphQLNonNull(GraphQLString())],
        ],
        'resolve'     => function ($source, $args, $context, $info): TypeInterface {
            /** @var SchemaInterface $schema */
            [$name] = $args;
            [$schema] = $info;
            return $schema->getType($name);
        }
    ]);
}

/**
 * @return Field
 * @throws \TypeError
 */
function TypeNameMetaFieldDefinition(): Field
{
    return new Field([
        'name'        => '__typename',
        'type'        => GraphQLNonNull(GraphQLString()),
        'description' => 'The name of the current Object type at runtime.',
        'resolve'     => function ($source, $args, $context, $info): string {
            /** @var NamedTypeInterface $parentType */
            [$parentType] = $info;
            return $parentType->getName();
        }
    ]);
}

/**
 * @return array
 */
function introspectionTypes(): array
{
    return [
        __Schema(),
        __Directive(),
        __DirectiveLocation(),
        __Type(),
        __Field(),
        __InputValue(),
        __EnumValue(),
        __TypeKind(),
    ];
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isIntrospectionType(TypeInterface $type): bool
{
    return arraySome(
        introspectionTypes(),
        function (TypeInterface $introspectionType) use ($type) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $type->getName() === $introspectionType->getName();
        }
    );
}
