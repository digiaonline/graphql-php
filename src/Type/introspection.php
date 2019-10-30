<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\Field;
use GraphQL\Contracts\TypeSystem\Type\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return ObjectType
 */
function __Schema(): ObjectType
{
    return GraphQL::make(GraphQL::SCHEMA_INTROSPECTION);
}

/**
 * @return ObjectType
 */
function __Directive(): ObjectType
{
    return GraphQL::make(GraphQL::DIRECTIVE_INTROSPECTION);

}

/**
 * @return EnumType
 */
function __DirectiveLocation(): EnumType
{
    return GraphQL::make(GraphQL::DIRECTIVE_LOCATION_INTROSPECTION);

}

/**
 * @return ObjectType
 */
function __Type(): ObjectType
{
    return GraphQL::make(GraphQL::TYPE_INTROSPECTION);
}

/**
 * @return ObjectType
 */
function __Field(): ObjectType
{
    return GraphQL::make(GraphQL::FIELD_INTROSPECTION);
}

/**
 * @return ObjectType
 */
function __InputValue(): ObjectType
{
    return GraphQL::make(GraphQL::INPUT_VALUE_INTROSPECTION);
}

/**
 * @return ObjectType
 */
function __EnumValue(): ObjectType
{
    return GraphQL::make(GraphQL::ENUM_VALUE_INTROSPECTION);
}

/**
 * @return EnumType
 */
function __TypeKind(): EnumType
{
    return GraphQL::make(GraphQL::TYPE_KIND_INTROSPECTION);
}

/**
 * @return Field
 */
function SchemaMetaFieldDefinition(): Field
{
    return GraphQL::make(GraphQL::SCHEMA_META_FIELD_DEFINITION);
}

/**
 * @return Field
 */
function TypeMetaFieldDefinition(): Field
{
    return GraphQL::make(GraphQL::TYPE_META_FIELD_DEFINITION);
}

/**
 * @return Field
 */
function TypeNameMetaFieldDefinition(): Field
{
    return GraphQL::make(GraphQL::TYPE_NAME_META_FIELD_DEFINITION);
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
 * @param NamedTypeInterface $type
 * @return bool
 */
function isIntrospectionType(NamedTypeInterface $type): bool
{
    return arraySome(
        introspectionTypes(),
        function (NamedTypeInterface $introspectionType) use ($type) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $type->getName() === $introspectionType->getName();
        }
    );
}
