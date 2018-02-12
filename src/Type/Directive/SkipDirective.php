<?php

namespace Digia\GraphQL\Type\Directive;

use Digia\GraphQL\Language\AST\DirectiveLocationEnum;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\Scalar\BooleanType;

class SkipDirective extends Directive
{

    /**
     * @inheritdoc
     * @throws \Exception
     * @throws \TypeError
     */
    protected function beforeConfig(): void
    {
        $this->setName('skip');
        $this->setDescription('Directs the executor to skip this field or fragment when the `if` ' .
            'argument is true.');
        $this->setLocations([
            DirectiveLocationEnum::FIELD,
            DirectiveLocationEnum::FRAGMENT_SPREAD,
            DirectiveLocationEnum::INLINE_FRAGMENT,
        ]);
        $this->setArgs([
            'if' => [
                'type'        => new NonNullType(new BooleanType()),
                'description' => 'Skipped when true.',
            ],
        ]);
    }
}
