<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\Contract\ValueNodeInterface;

trait DefaultValueTrait
{

    /**
     * @var ValueNodeInterface
     */
    protected $defaultValue;

    /**
     * @return string
     */
    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }
}
