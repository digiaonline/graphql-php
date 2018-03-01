<?php

namespace Digia\GraphQL\Language\AST\Node;

trait AliasTrait
{

    /**
     * @var NameNode|null
     */
    protected $alias;

    /**
     * @return NameNode|null
     */
    public function getAlias(): ?NameNode
    {
        return $this->alias;
    }

    /**
     * @return array|null
     */
    public function getAliasAsArray(): ?array
    {
        return null !== $this->alias ? $this->alias->toArray() : null;
    }
}
