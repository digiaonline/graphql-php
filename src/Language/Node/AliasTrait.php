<?php

namespace Digia\GraphQL\Language\Node;

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
     * @param NameNode|null $alias
     *
     * @return $this
     */
    public function setAlias(?NameNode $alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAliasOrNameValue()
    {
        return $this->getAliasValue() ?? $this->getNameValue();
    }

    /**
     * @return null|string
     */
    public function getAliasValue(): ?string
    {
        return null !== $this->alias ? $this->alias->getValue() : null;
    }

    /**
     * @return array|null
     */
    public function getAliasAsArray(): ?array
    {
        return null !== $this->alias ? $this->alias->toArray() : null;
    }
}
