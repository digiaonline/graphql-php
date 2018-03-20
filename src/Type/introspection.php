<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return ObjectType
 */
function __Schema(): ObjectType
{
    return GraphQL::get(GraphQL::SCHEMA_INTROSPECTION);
}

/**
 * @return ObjectType
 */
function __Directive(): ObjectType
{
    return GraphQL::get(GraphQL::DIRECTIVE_INTROSPECTION);

}

/**
 * @return EnumType
 */
function __DirectiveLocation(): EnumType
{
    return GraphQL::get(GraphQL::DIRECTIVE_LOCATION_INTROSPECTION);

}

/**
 * @return ObjectType
 */
function __Type(): ObjectType
{
    return GraphQL::get(GraphQL::TYPE_INTROSPECTION);
}

/**
 * @return ObjectType
 */
function __Field(): ObjectType
{
    return GraphQL::get(GraphQL::FIELD_INTROSPECTION);
}

/**
 * @return ObjectType
 */
function __InputValue(): ObjectType
{
    return GraphQL::get(GraphQL::INPUT_VALUE_INTROSPECTION);
}

/**
 * @return ObjectType
 */
function __EnumValue(): ObjectType
{
    return GraphQL::get(GraphQL::ENUM_VALUE_INTROSPECTION);
}

/**
 * @return EnumType
 */
function __TypeKind(): EnumType
{
    return GraphQL::get(GraphQL::TYPE_KIND_INTROSPECTION);
}

/**
 * @return Field
 */
function SchemaMetaFieldDefinition(): Field
{
    return GraphQL::get(GraphQL::SCHEMA_META_FIELD_DEFINITION);
}

/**
 * @return Field
 */
function TypeMetaFieldDefinition(): Field
{
    return GraphQL::get(GraphQL::TYPE_META_FIELD_DEFINITION);
}

/**
 * @return Field
 */
function TypeNameMetaFieldDefinition(): Field
{
    return GraphQL::get(GraphQL::TYPE_NAME_META_FIELD_DEFINITION);
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
