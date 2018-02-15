<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\FieldDefinitionNode;

trait FieldsTrait
{

    /**
     * @var FieldDefinitionNode[]
     */
    protected $fields;

    /**
     * @return FieldDefinitionNode[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
