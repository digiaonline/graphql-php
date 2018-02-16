<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\NameNode;

trait NameTrait
{

    /**
     * @var NameNode
     */
    protected $name;

    /**
     * @return NameNode
     */
    public function getName(): NameNode
    {
        return $this->name;
    }
}
