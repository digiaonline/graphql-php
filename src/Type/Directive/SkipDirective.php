<?php

namespace Digia\GraphQL\Type\Directive;

use Digia\GraphQL\Language\AST\DirectiveLocationEnum;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\Scalar\BooleanType;

class SkipDirective extends Directive
{

    /**
     * @inheritdoc
     * @throws \TypeError
     */
    public function configure(): array
    {
        return [
            'name' => 'skip',
            'description' =>
                'Directs the executor to skip this field or fragment when the `if` ' .
                'argument is true.',
            'locations' => [
                DirectiveLocationEnum::FIELD,
                DirectiveLocationEnum::FRAGMENT_SPREAD,
                DirectiveLocationEnum::INLINE_FRAGMENT,
            ],
            'arguments' => [
                [
                    'name'        => 'if',
                    'type'        => new NonNullType(new BooleanType()),
                    'description' => 'Skipped when true.',
                ],
            ],
        ];
    }
}
