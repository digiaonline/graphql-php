<?php

namespace Digia\GraphQL\Type\Directive;

use Digia\GraphQL\Language\AST\DirectiveLocationEnum;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\Scalar\BooleanType;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\GraphQLNonNull;

class IncludeDirective extends Directive
{

    /**
     * @inheritdoc
     * @throws \Exception
     * @throws \TypeError
     */
    protected function beforeConfig(): void
    {
        $this->setName('include');
        $this->setDescription('Directs the executor to include this field or fragment only when ' .
            'the `if` argument is true.');
        $this->setLocations([
            DirectiveLocationEnum::FIELD,
            DirectiveLocationEnum::FRAGMENT_SPREAD,
            DirectiveLocationEnum::INLINE_FRAGMENT,
        ]);
        $this->setArgs([
            'if ' => [
                'type'        => GraphQLNonNull(GraphQLBoolean()),
                'description' => 'Included when true.',
            ]
        ]);
    }
}
