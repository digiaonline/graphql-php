<?php

namespace Digia\GraphQL\Type\Directive;

use Digia\GraphQL\Type\Definition\BooleanType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\TypeSystem\AbstractDirective;
use Digia\GraphQL\TypeSystem\Directive\DirectiveLocationEnum;

class IncludeDirective extends AbstractDirective
{

    /**
     * @var string
     */
    protected $name = 'include';

    /**
     * @var string
     */
    protected $description =
        'Directs the executor to include this field or fragment only when ' .
        'the `if` argument is true.';

    /**
     * @inheritdoc
     * @throws \TypeError
     */
    public function configure(): array
    {
        return [
            'locations' => [
                DirectiveLocationEnum::FIELD,
                DirectiveLocationEnum::FRAGMENT_SPREAD,
                DirectiveLocationEnum::INLINE_FRAGMENT,
            ],
            'arguments' => [
                [
                    'name'        => 'if',
                    'type'        => new NonNullType(new BooleanType()),
                    'description' => 'Included when true.',
                ],
            ]
        ];
    }
}
