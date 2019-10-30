<?php

namespace Digia\GraphQL\Type\Definition;

use GraphQL\Contracts\TypeSystem\Common\NameAwareInterface;

/**
 * @mixin NameAwareInterface
 */
trait NameTrait
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
