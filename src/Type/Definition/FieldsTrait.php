<?php

namespace Digia\GraphQL\Type\Definition;

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
     */
    protected function addField(Field $field): void
    {
        $this->fields[] = $field;
    }

    /**
     * @param array $fields
     */
    protected function setFields(array $fields): void
    {
        array_map(function ($config) {
            $this->addField(new Field($config));
        }, $fields);
    }
}
