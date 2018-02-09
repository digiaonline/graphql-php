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
    private $_fieldMap;

    /**
     * @param string $name
     * @return Field|null
     * @throws \Exception
     */
    public function getField(string $name): ?Field
    {
        $this->buildFieldMap();

        return $this->_fieldMap[$name] ?? null;
    }

    /**
     * @return Field[]
     * @throws \Exception
     */
    public function getFields(): array
    {
        $this->buildFieldMap();

        return $this->_fieldMap ?? [];
    }

    /**
     * @param Field $field
     * @return $this
     * @throws \Exception
     */
    public function addField(Field $field)
    {
        $this->buildFieldMap();

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
     * @throws \Exception
     */
    protected function buildFieldMap()
    {
        // Fields are lazy-loaded to avoid concurrency issues.
        if ($this->_fieldMap === null) {
            $this->_fieldMap = defineFieldMap($this, $this->_fieldsThunk);
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
}

/**
 * @param mixed $type
 * @param mixed $fields
 * @return array
 * @throws \Exception
 */
function defineFieldMap($type, $fieldsThunk): array
{
    $fields = resolveThunk($fieldsThunk) ?: [];

    invariant(
        isAssocArray($fields),
        sprintf(
            '%s fields must be an associative array with field names as key or a callable which returns such an array.',
            $type->getName()
        )
    );

    $fieldMap = [];

    foreach ($fields as $fieldName => $fieldConfig) {
        invariant(
            is_array($fieldConfig),
            sprintf('%s.%s field config must be an object', $type->getName(), $fieldName)
        );

        invariant(
            !isset($fieldConfig['isDeprecated']),
            sprintf('%s.%s should provide "deprecationReason" instead of "isDeprecated".', $type->getName(), $fieldName)
        );

        $fieldMap[$fieldName] = new Field(array_merge($fieldConfig, ['name' => $fieldName]));
    }

    return $fieldMap;
}
