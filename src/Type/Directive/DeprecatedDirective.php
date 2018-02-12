<?php

namespace Digia\GraphQL\Type\Directive;

use Digia\GraphQL\Language\AST\DirectiveLocationEnum;
use Digia\GraphQL\Type\Definition\Scalar\StringType;
use function Digia\GraphQL\Type\GraphQLString;

class DeprecatedDirective extends Directive
{

    const DEFAULT_DEPRECATION_VALUE = 'No longer supported';

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function beforeConfig(): void
    {
        $this->setName('deprecated');
        $this->setDescription('Marks an element of a GraphQL schema as no longer supported.');
        $this->setLocations([
            DirectiveLocationEnum::FIELD_DEFINITION,
            DirectiveLocationEnum::ENUM_VALUE,
        ]);
        $this->setArgs([
            'reason' => [
                'type'         => GraphQLString(),
                'description'  =>
                    'Explains why this element was deprecated, usually also including a ' .
                    'suggestion for how to access supported similar data. Formatted ' .
                    'in [Markdown](https://daringfireball.net/projects/markdown/).',
                'defaultValue' => self::DEFAULT_DEPRECATION_VALUE,
            ],
        ]);
    }
}
