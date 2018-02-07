<?php

namespace Digia\GraphQL\Type\Directive;

use Digia\GraphQL\Type\Definition\StringType;
use Digia\GraphQL\TypeSystem\AbstractDirective;
use Digia\GraphQL\TypeSystem\Directive\DirectiveLocationEnum;

class DeprecatedDirective extends AbstractDirective
{

    const DEFAULT_DEPRECATION_VALUE = 'No longer supported';

    /**
     * @var string
     */
    protected $name = 'deprecated';

    /**
     * @var string
     */
    protected $description = 'Marks an element of a GraphQL schema as no longer supported.';

    /**
     * @inheritdoc
     */
    public function configure(): array
    {
        return [
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
