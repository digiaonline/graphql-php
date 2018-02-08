<?php

namespace Digia\GraphQL\Type\Definition\Behavior;

use Digia\GraphQL\Type\Definition\Field;

trait FieldsTrait
{

    /**
     * @var Field[]
     */
    private $fields = [];

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param Field $field
     * @return $this
     */
    protected function addField(Field $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    protected function addFields(array $fields)
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

    /**
     * @param Field[] $fields
     * @return $this
     */
    protected function setFields(array $fields)
    {
        $this->addFields(array_map(function ($config) {
            return new Field($config);
        }, $fields));

        return $this;
    }
}
