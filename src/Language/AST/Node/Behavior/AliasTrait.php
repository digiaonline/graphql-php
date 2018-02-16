<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\NameNode;

trait AliasTrait
{

    /**
     * @var ?NameNode
     */
    protected $alias;

    /**
     * @return NameNode|null
     */
    public function getAlias(): ?NameNode
    {
        return $this->alias;
    }
}
