<?php

namespace Digia\GraphQL\Language\AST\Node;

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
     * @return null|string
     */
    public function getDescriptionValue(): ?string
    {
        return null !== $this->description ? $this->description->getValue() : null;
    }

    /**
     * @return array|null
     */
    public function getDescriptionAsArray(): ?array
    {
        return null !== $this->description ? $this->description->toArray() : null;
    }
}
