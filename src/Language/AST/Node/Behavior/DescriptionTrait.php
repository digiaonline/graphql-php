<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

trait DescriptionTrait
{

    /**
     * @var ?string
     */
    protected $description;

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }
}
