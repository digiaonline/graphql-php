<?php

namespace Digia\GraphQL\Type\Directive;

use Digia\GraphQL\Language\AST\DirectiveLocationEnum;
use Digia\GraphQL\Type\Definition\Scalar\StringType;

class DeprecatedDirective extends AbstractDirective
{

    const DEFAULT_DEPRECATION_VALUE = 'No longer supported';

    /**
     * @inheritdoc
     */
    public function configure(): array
    {
        return [
            'name' => 'deprecated',
            'description' => 'Marks an element of a GraphQL schema as no longer supported.',
            'locations' => [
                DirectiveLocationEnum::FIELD_DEFINITION,
                DirectiveLocationEnum::ENUM_VALUE,
            ],
            'arguments' => [
                [
                    'name'         => 'reason',
                    'type'         => new StringType(),
                    'description'  =>
                        'Explains why this element was deprecated, usually also including a ' .
                        'suggestion for how to access supported similar data. Formatted ' .
                        'in [Markdown](https://daringfireball.net/projects/markdown/).',
                    'defaultValue' => self::DEFAULT_DEPRECATION_VALUE,
                ],
            ],
        ];
    }

}
