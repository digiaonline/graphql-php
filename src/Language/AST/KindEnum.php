<?php

namespace Digia\GraphQL\Language\AST;

class KindEnum
{

    // Name
    const NAME = 'Name';

    // Document
    const DOCUMENT             = 'Document';
    const OPERATION_DEFINITION = 'OperationDefinition';
    const VARIABLE_DEFINITION  = 'VariableDefinition';
    const VARIABLE             = 'Variable';
    const SELECTION_SET        = 'SelectionSet';
    const FIELD                = 'Field';
    const ARGUMENT             = 'Argument';

    // Fragments
    const FRAGMENT_SPREAD     = 'FragmentSpread';
    const INLINE_FRAGMENT     = 'InlineFragment';
    const FRAGMENT_DEFINITION = 'FragmentDefinition';

    // Values
    const INT          = 'IntValue';
    const FLOAT        = 'FloatValue';
    const STRING       = 'StringValue';
    const BOOLEAN      = 'BooleanValue';
    const NULL         = 'NullValue';
    const ENUM         = 'EnumValue';
    const LIST         = 'ListValue';
    const OBJECT       = 'ObjectValue';
    const OBJECT_FIELD = 'ObjectField';

    // Directives
    const DIRECTIVE = 'Directive';

    // Types
    const NAMED_TYPE    = 'NamedType';
    const LIST_TYPE     = 'ListType';
    const NON_NULL_TYPE = 'NonNullType';

    // Type System Definitions
    const SCHEMA_DEFINITION         = 'SchemaDefinition';
    const OPERATION_TYPE_DEFINITION = 'OperationTypeDefinition';

    // Type Definitions
    const SCALAR_TYPE_DEFINITION       = 'ScalarTypeDefinition';
    const OBJECT_TYPE_DEFINITION       = 'ObjectTypeDefinition';
    const FIELD_DEFINITION             = 'FieldDefinition';
    const INPUT_VALUE_DEFINITION       = 'InputValueDefinition';
    const INTERFACE_TYPE_DEFINITION    = 'InterfaceTypeDefinition';
    const UNION_TYPE_DEFINITION        = 'UnionTypeDefinition';
    const ENUM_TYPE_DEFINITION         = 'EnumTypeDefinition';
    const ENUM_VALUE_DEFINITION        = 'EnumValueDefinition';
    const INPUT_OBJECT_TYPE_DEFINITION = 'InputObjectTypeDefinition';

    // Type Extensions
    const SCALAR_TYPE_EXTENSION       = 'ScalarTypeExtension';
    const OBJECT_TYPE_EXTENSION       = 'ObjectTypeExtension';
    const INTERFACE_TYPE_EXTENSION    = 'InterfaceTypeExtension';
    const UNION_TYPE_EXTENSION        = 'UnionTypeExtension';
    const ENUM_TYPE_EXTENSION         = 'EnumTypeExtension';
    const INPUT_OBJECT_TYPE_EXTENSION = 'InputObjectTypeExtension';

    // Directive Definitions
    const DIRECTIVE_DEFINITION = 'DirectiveDefinition';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
