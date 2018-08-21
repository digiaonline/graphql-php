<?php

namespace Digia\GraphQL\Language\Node;

class NodeKindEnum
{

    // Name
    public const NAME = 'Name';

    // Document
    public const DOCUMENT             = 'Document';
    public const OPERATION_DEFINITION = 'OperationDefinition';
    public const VARIABLE_DEFINITION  = 'VariableDefinition';
    public const VARIABLE             = 'Variable';
    public const SELECTION_SET        = 'SelectionSet';
    public const FIELD                = 'Field';
    public const ARGUMENT             = 'Argument';

    // Fragments
    public const FRAGMENT_SPREAD     = 'FragmentSpread';
    public const INLINE_FRAGMENT     = 'InlineFragment';
    public const FRAGMENT_DEFINITION = 'FragmentDefinition';

    // Values
    public const INT          = 'IntValue';
    public const FLOAT        = 'FloatValue';
    public const STRING       = 'StringValue';
    public const BOOLEAN      = 'BooleanValue';
    public const NULL         = 'NullValue';
    public const ENUM         = 'EnumValue';
    public const LIST         = 'ListValue';
    public const OBJECT       = 'ObjectValue';
    public const OBJECT_FIELD = 'ObjectField';

    // Directives
    public const DIRECTIVE = 'Directive';

    // Types
    public const NAMED_TYPE    = 'NamedType';
    public const LIST_TYPE     = 'ListType';
    public const NON_NULL_TYPE = 'NonNullType';

    // Type System Definitions
    public const SCHEMA_DEFINITION         = 'SchemaDefinition';
    public const OPERATION_TYPE_DEFINITION = 'OperationTypeDefinition';

    // Type Definitions
    public const SCALAR_TYPE_DEFINITION       = 'ScalarTypeDefinition';
    public const OBJECT_TYPE_DEFINITION       = 'ObjectTypeDefinition';
    public const FIELD_DEFINITION             = 'FieldDefinition';
    public const INPUT_VALUE_DEFINITION       = 'InputValueDefinition';
    public const INTERFACE_TYPE_DEFINITION    = 'InterfaceTypeDefinition';
    public const UNION_TYPE_DEFINITION        = 'UnionTypeDefinition';
    public const ENUM_TYPE_DEFINITION         = 'EnumTypeDefinition';
    public const ENUM_VALUE_DEFINITION        = 'EnumValueDefinition';
    public const INPUT_OBJECT_TYPE_DEFINITION = 'InputObjectTypeDefinition';

    // Directive Definitions
    public const DIRECTIVE_DEFINITION = 'DirectiveDefinition';

    // Type System Extensions
    public const SCHEMA_EXTENSION = 'SchemaExtension';

    // Type Extensions
    public const SCALAR_TYPE_EXTENSION       = 'ScalarTypeExtension';
    public const OBJECT_TYPE_EXTENSION       = 'ObjectTypeExtension';
    public const INTERFACE_TYPE_EXTENSION    = 'InterfaceTypeExtension';
    public const UNION_TYPE_EXTENSION        = 'UnionTypeExtension';
    public const ENUM_TYPE_EXTENSION         = 'EnumTypeExtension';
    public const INPUT_OBJECT_TYPE_EXTENSION = 'InputObjectTypeExtension';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
