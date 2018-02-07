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
     * @return $this
     */
    protected function addField(Field $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @param Field[] $fields
     * @return $this
     */
    protected function setFields(array $fields)
    {
        foreach ($fields as $config) {
            $this->addField(new Field($config));
        }

        return $this;
    }
}
