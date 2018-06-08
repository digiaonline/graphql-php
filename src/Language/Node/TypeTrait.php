<?php

namespace Digia\GraphQL\Language\Node;

trait TypeTrait
{
    /**
     * @var TypeNodeInterface
     */
    protected $type;

    /**
     * @return TypeNodeInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getTypeAST(): array
    {
        return $this->type->toAST();
    }

    /**
     * @param TypeNodeInterface $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
