<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\resolveThunk;
use function Digia\GraphQL\Util\invariant;
use function Digia\GraphQL\Util\toString;

trait FieldsTrait
{
    /**
     * Fields can be defined either as an array or as a thunk.
     * Using thunks allows for cross-referencing of fields.
     *
     * @var array|callable
     */
    protected $fieldsOrThunk;

    /**
     * A key-value map over field names and their corresponding field instances.
     *
     * @var Field[]
     */
    protected $fieldMap;

    /**
     * @param string $fieldName
     * @return Field|null
     * @throws InvariantException
     */
    public function getField(string $fieldName): ?Field
    {
        return $this->getFields()[$fieldName] ?? null;
    }

    /**
     * @return Field[]
     * @throws InvariantException
     */
    public function getFields(): array
    {
        // Fields are built lazily to avoid concurrency issues.
        if (!isset($this->fieldMap)) {
            $this->fieldMap = $this->buildFieldMap($this->fieldsOrThunk);
        }
        return $this->fieldMap;
    }

    /**
     * @param array|callable $fieldsOrThunk
     * @return $this
     */
    protected function setFields($fieldsOrThunk)
    {
        $this->fieldsOrThunk = $fieldsOrThunk;
        return $this;
    }

    /**
     * @param mixed $fieldsOrThunk
     * @return array
     * @throws InvariantException
     */
    protected function buildFieldMap($fieldsOrThunk): array
    {
        $fields = resolveThunk($fieldsOrThunk);

        invariant(
            isAssocArray($fields),
            \sprintf(
                '%s fields must be an associative array with field names as key or a callable which returns such an array.',
                $this->name
            )
        );

        $fieldMap = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            invariant(
                \is_array($fieldConfig),
                \sprintf('%s.%s field config must be an array', $this->name, $fieldName)
            );

            invariant(
                !isset($fieldConfig['isDeprecated']),
                \sprintf(
                    '%s.%s should provide "deprecationReason" instead of "isDeprecated".',
                    $this->name,
                    $fieldName
                )
            );

            if (isset($fieldConfig['resolve'])) {
                invariant(
                    null === $fieldConfig['resolve'] || \is_callable($fieldConfig['resolve']),
                    \sprintf(
                        '%s.%s field resolver must be a function if provided, but got: %s',
                        $this->name,
                        $fieldName,
                        toString($fieldConfig['resolve'])
                    )
                );
            }

            $fieldMap[$fieldName] = new Field(
                $fieldName,
                $fieldConfig['description'] ?? null,
                $fieldConfig['type'] ?? null,
                $fieldConfig['args'] ?? [],
                $fieldConfig['resolve'] ?? null,
                $fieldConfig['deprecationReason'] ?? null,
                $fieldConfig['astNode'] ?? null
            );
        }

        return $fieldMap;
    }
}
