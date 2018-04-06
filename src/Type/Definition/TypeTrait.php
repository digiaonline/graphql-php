<?php

namespace Digia\GraphQL\Type\Definition;

trait TypeTrait
{

    /**
     * @var mixed
     */
    protected $type;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Note: We do not type-hint the `$type`, because we want the
     * `SchemaValidator` to catch these errors.
     *
     * @param mixed $type
     *
     * @return $this
     */
    protected function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
