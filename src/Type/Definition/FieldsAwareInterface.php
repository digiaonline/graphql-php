<?php

namespace Digia\GraphQL\Type\Definition;

interface FieldsAwareInterface
{
    /**
     * @param string $fieldName
     * @return Field|null
     */
    public function getField(string $fieldName): ?Field;

    /**
     * @return Field[]
     */
    public function getFields(): array;
}
