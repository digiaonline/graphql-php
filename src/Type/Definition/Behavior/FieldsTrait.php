<?php

namespace Digia\GraphQL\Type\Definition\Behavior;

use Digia\GraphQL\Type\Definition\Field;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\resolveThunk;
use function Digia\GraphQL\Util\invariant;

trait FieldsTrait
{

    /**
     * @var array|callable
     */
    private $_fieldsThunk;

    /**
     * @var Field[]
     */
    private $_fieldMap = [];

    /**
     * @var bool
     */
    private $_isFieldMapDefined = false;

    /**
     * @param Field $field
     * @return $this
     * @throws \Exception
     */
    public function addField(Field $field)
    {
        $this->_fieldMap[$field->getName()] = $field;

        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function addFields(array $fields)
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

    /**
     * @return Field[]
     * @throws \Exception
     */
    public function getFields(): array
    {
        $this->defineFieldMapIfNecessary();

        return $this->_fieldMap;
    }

    /**
     * @throws \Exception
     */
    protected function defineFieldMapIfNecessary(): void
    {
        // Fields are built lazily to avoid concurrency issues.
        if (!$this->_isFieldMapDefined) {
            $this->_fieldMap = array_merge($this->defineFieldMap($this->_fieldsThunk), $this->_fieldMap);

            $this->_isFieldMapDefined = true;
        }
    }

    /**
     * @param array|callable $fieldsThunk
     * @return $this
     */
    protected function setFields($fieldsThunk)
    {
        $this->_fieldsThunk = $fieldsThunk;

        return $this;
    }

    /**
     * @param mixed $fieldsThunk
     * @return array
     * @throws \Exception
     */
    protected function defineFieldMap($fieldsThunk): array
    {
        $fields = resolveThunk($fieldsThunk) ?: [];

        invariant(
            isAssocArray($fields),
            sprintf(
                '%s fields must be an associative array with field names as key or a callable which returns such an array.',
                $this->getName()
            )
        );

        $fieldMap = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            invariant(
                is_array($fieldConfig),
                sprintf('%s.%s field config must be an object', $this->getName(), $fieldName)
            );

            invariant(
                !isset($fieldConfig['isDeprecated']),
                sprintf(
                    '%s.%s should provide "deprecationReason" instead of "isDeprecated".',
                    $this->getName(),
                    $fieldName
                )
            );

            $fieldMap[$fieldName] = new Field(array_merge($fieldConfig, ['name' => $fieldName]));
        }

        return $fieldMap;
    }

}
