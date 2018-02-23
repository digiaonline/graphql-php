<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;

trait ArgumentsTrait
{

    /**
     * @var InputValueDefinitionNode[]
     */
    protected $arguments;

    /**
     * @return InputValueDefinitionNode[]
     */
    public function getArguments(): array
    {
        return $this->arguments ?? [];
    }
}
