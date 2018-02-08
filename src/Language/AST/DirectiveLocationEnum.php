<?php

namespace Digia\GraphQL\Language\AST;

class DirectiveLocationEnum
{

    // Request Definitions
    const QUERY               = 'QUERY';
    const MUTATION            = 'MUTATION';
    const SUBSCRIPTION        = 'SUBSCRIPTION';
    const FIELD               = 'FIELD';
    const FRAGMENT_DEFINITION = 'FRAGMENT_DEFINITION';
    const FRAGMENT_SPREAD     = 'FRAGMENT_SPREAD';
    const INLINE_FRAGMENT     = 'INLINE_FRAGMENT';
    // Type System Definitions
    const SCHEMA                 = 'SCHEMA';
    const SCALAR                 = 'SCALAR';
    const OBJECT                 = 'OBJECT';
    const FIELD_DEFINITION       = 'FIELD_DEFINITION';
    const ARGUMENT_DEFINITION    = 'ARGUMENT_DEFINITION';
    const INTERFACE              = 'INTERFACE';
    const UNION                  = 'UNION';
    const ENUM                   = 'ENUM';
    const ENUM_VALUE             = 'ENUM_VALUE';
    const INPUT_OBJECT           = 'INPUT_OBJECT';
    const INPUT_FIELD_DEFINITION = 'INPUT_FIELD_DEFINITION';
}
