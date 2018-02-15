<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\StringValueNode;

trait DescriptionTrait
{

    /**
     * @var ?StringValueNode
     */
    protected $description;

    /**
     * @return StringValueNode
     */
    public function getDescription(): ?StringValueNode
    {
        return $this->description;
    }
}
