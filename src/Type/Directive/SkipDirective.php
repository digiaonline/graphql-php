<?php

namespace Digia\GraphQL\Type\Directive;

use Digia\GraphQL\Type\Definition\BooleanType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\TypeSystem\AbstractDirective;
use Digia\GraphQL\TypeSystem\Directive\DirectiveLocationEnum;

class SkipDirective extends AbstractDirective
{

    /**
     * @var string
     */
    protected $name = 'skip';

    /**
     * @var string
     */
    protected $description =
        'Directs the executor to skip this field or fragment when the `if` ' .
        'argument is true.';

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
                    'description' => 'Skipped when true.',
                ],
            ],
        ];
    }

}
