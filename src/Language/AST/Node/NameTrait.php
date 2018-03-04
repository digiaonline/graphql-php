<?php

namespace Digia\GraphQL\Language\AST\Node;

trait NameTrait
{

    /**
     * @var NameNode|null
     */
    protected $name;

    /**
     * @return NameNode|null
     */
    public function getName(): ?NameNode
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getNameValue(): ?string
    {
        return null !== $this->name ? $this->name->getValue() : null;
    }

    /**
     * @return array|null
     */
    public function getNameAsArray(): ?array
    {
        return null !== $this->name ? $this->name->toArray() : null;
    }

    /**
     * @param NameNode|null $name
     * @return $this
     */
    public function setName(?NameNode $name)
    {
        $this->name = $name;
        return $this;
    }
}
