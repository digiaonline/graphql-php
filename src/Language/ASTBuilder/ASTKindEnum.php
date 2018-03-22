<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Util\AbstractEnum;

/**
 * Abstract Syntax Tree Kinds
 *
 * Each of these kinds has a separate AST builder that takes care of parsing the associated AST.
 * Names are in *snake_case* to prevent them from getting mixed up with `NodeKindEnum` values.
 */
class ASTKindEnum extends AbstractEnum
{
    // Standard
    public const DOCUMENT             = 'document';
    public const OPERATION_DEFINITION = 'operation_definition';
    public const SELECTION_SET        = 'selection_set';
    public const VARIABLE_DEFINITION  = 'variable_definition';
    public const FRAGMENT             = 'fragment';
    public const FIELD                = 'field';
    public const NAME                 = 'name';
    public const NAMED_TYPE           = 'named_type';
    public const TYPE_REFERENCE       = 'type_reference';
    public const DIRECTIVES           = 'directives';
    public const ARGUMENTS            = 'arguments';
    public const VALUE_LITERAL        = 'value_literal';
    public const STRING_LITERAL       = 'string_literal';
    public const VARIABLE             = 'variable';

    // Schema Definition Language
    public const SCHEMA_DEFINITION            = 'schema_definition';
    public const SCALAR_TYPE_DEFINITION       = 'scalar_type_definition';
    public const SCALAR_TYPE_EXTENSION        = 'scalar_type_extension';
    public const OBJECT_TYPE_DEFINITION       = 'object_type_definition';
    public const OBJECT_TYPE_EXTENSION        = 'object_type_extension';
    public const IMPLEMENTS_INTERFACES        = 'implements_interfaces';
    public const INTERFACE_TYPE_DEFINITION    = 'interface_type_definition';
    public const INTERFACE_TYPE_EXTENSION     = 'interface_type_extension';
    public const ENUM_TYPE_EXTENSION          = 'enum_type_extension';
    public const ENUM_TYPE_DEFINITION         = 'enum_type_definition';
    public const ENUM_VALUES_DEFINITION       = 'enum_values_definition';
    public const UNION_TYPE_DEFINITION        = 'union_type_definition';
    public const UNION_TYPE_EXTENSION         = 'union_type_extension';
    public const UNION_MEMBER_TYPES           = 'union_member_types';
    public const INPUT_OBJECT_TYPE_DEFINITION = 'input_object_type_definition';
    public const INPUT_OBJECT_TYPE_EXTENSION  = 'input_object_type_extension';
    public const INPUT_FIELDS_DEFINITION      = 'input_fields_definition';
    public const INPUT_VALUE_DEFINITION       = 'input_value_definition';
    public const DIRECTIVE_DEFINITION         = 'directive_definition';
    public const DESCRIPTION                  = 'description';
    public const FRAGMENT_DEFINITION          = 'fragment_definition';
    public const FIELDS_DEFINITION            = 'fields_definition';
    public const ARGUMENTS_DEFINITION         = 'arguments_definition';
}
