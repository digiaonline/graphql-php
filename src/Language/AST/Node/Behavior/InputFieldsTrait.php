<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;

trait InputFieldsTrait
{

    /**
     * @var InputValueDefinitionNode[]
     */
    protected $fields;

    /**
     * @return InputValueDefinitionNode[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
