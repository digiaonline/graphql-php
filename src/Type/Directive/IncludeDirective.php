<?php

namespace Digia\GraphQL\Type\Directive;

use Digia\GraphQL\Type\Definition\BooleanType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\TypeSystem\AbstractDirective;
use Digia\GraphQL\TypeSystem\Directive\DirectiveLocationEnum;

class IncludeDirective extends AbstractDirective
{

    /**
     * @inheritdoc
     * @throws \TypeError
     */
    public function configure(): array
    {
        return [
            'name'        => 'include',
            'description' =>
                'Directs the executor to include this field or fragment only when ' .
                'the `if` argument is true.',
            'locations'   => [
                DirectiveLocationEnum::FIELD,
                DirectiveLocationEnum::FRAGMENT_SPREAD,
                DirectiveLocationEnum::INLINE_FRAGMENT,
            ],
            'arguments'   => [
                [
                    'name'        => 'if',
                    'type'        => new NonNullType(new BooleanType()),
                    'description' => 'Included when true.',
                ],
            ]
        ];
    }
}
