<?php

namespace Digia\GraphQL\Language\Node;

trait AliasTrait
{

    /**
     * @var NameNode|null
     */
    protected $alias;

    /**
     * @return null|string
     */
    abstract public function getNameValue(): ?string;

    /**
     * @return NameNode|null
     */
    public function getAlias(): ?NameNode
    {
        return $this->alias;
    }

    /**
     * @return null|string
     */
    public function getAliasValue(): ?string
    {
        return null !== $this->alias ? $this->alias->getValue() : null;
    }

    /**
     * @return null|string
     */
    public function getAliasOrNameValue(): ?string
    {
        return $this->getAliasValue() ?? $this->getNameValue();
    }

    /**
     * @return array|null
     */
    public function getAliasAST(): ?array
    {
        return null !== $this->alias ? $this->alias->toAST() : null;
    }

    /**
     * @param NameNode|null $alias
     * @return $this
     */
    public function setAlias(?NameNode $alias)
    {
        $this->alias = $alias;
        return $this;
    }
}
