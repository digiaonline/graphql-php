<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\EnumValueDefinitionNode;

trait ValuesTrait
{

    /**
     * @var EnumValueDefinitionNode[]
     */
    protected $values;

    /**
     * @return EnumValueDefinitionNode[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
