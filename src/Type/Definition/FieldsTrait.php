<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\isValidResolver;
use function Digia\GraphQL\Type\resolveThunk;
use function Digia\GraphQL\Util\invariant;
use function Digia\GraphQL\Util\toString;

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
     * @return Field[]
     */
    public function getFields(): array
    {
        $this->defineFieldMapIfNecessary();

        return $this->_fieldMap;
    }

    /**
     *
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
     * @throws InvariantException
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
                sprintf('%s.%s field config must be an array', $this->getName(), $fieldName)
            );

            invariant(
                !isset($fieldConfig['isDeprecated']),
                sprintf(
                    '%s.%s should provide "deprecationReason" instead of "isDeprecated".',
                    $this->getName(),
                    $fieldName
                )
            );

            if (isset($fieldConfig['resolve'])) {
                invariant(
                    isValidResolver($fieldConfig['resolve']),
                    sprintf(
                        '%s.%s field resolver must be a function if provided, but got: %s',
                        $this->getName(),
                        $fieldName,
                        toString($fieldConfig['resolve'])
                    )
                );
            }

            $fieldMap[$fieldName] = new Field(array_merge($fieldConfig, ['name' => $fieldName]));
        }

        return $fieldMap;
    }

}
