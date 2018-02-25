<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\StringValueNode;

trait DescriptionTrait
{

    /**
     * @var StringValueNode|null
     */
    protected $description;

    /**
     * @return StringValueNode|null
     */
    public function getDescription(): ?StringValueNode
    {
        return $this->description;
    }

    /**
     * @return array|null
     */
    public function getDescriptionAsArray(): ?array
    {
        return null !== $this->description ? $this->description->toArray() : null;
    }
}
