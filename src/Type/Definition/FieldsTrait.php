<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\newField;
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
    protected $rawFieldsOrThunk;

    /**
     * A key-value map over field names and their corresponding field instances.
     *
     * @var Field[]
     */
    protected $fieldMap;

    /**
     * @return null|string
     */
    abstract public function getName(): ?string;

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
            $this->fieldMap = $this->buildFieldMap($this->rawFieldsOrThunk);
        }

        return $this->fieldMap;
    }

    /**
     * @param array|callable $rawFieldsOrThunk
     * @return Field[]
     * @throws InvariantException
     */
    protected function buildFieldMap($rawFieldsOrThunk): array
    {
        $rawFields = resolveThunk($rawFieldsOrThunk);

        invariant(
            isAssocArray($rawFields),
            \sprintf(
                '%s fields must be an associative array with field names as keys or a ' .
                'callable which returns such an array.',
                $this->getName()
            )
        );

        $fieldMap = [];

        foreach ($rawFields as $fieldName => $fieldConfig) {
            invariant(
                \is_array($fieldConfig),
                \sprintf('%s.%s field config must be an associative array.', $this->getName(), $fieldName)
            );

            invariant(
                !isset($fieldConfig['isDeprecated']),
                \sprintf(
                    '%s.%s should provide "deprecationReason" instead of "isDeprecated".',
                    $this->getName(),
                    $fieldName
                )
            );

            if (isset($fieldConfig['resolve'])) {
                invariant(
                    null === $fieldConfig['resolve'] || \is_callable($fieldConfig['resolve']),
                    \sprintf(
                        '%s.%s field resolver must be a function if provided, but got: %s.',
                        $this->getName(),
                        $fieldName,
                        toString($fieldConfig['resolve'])
                    )
                );
            }

            $fieldConfig['name']     = $fieldName;
            $fieldConfig['typeName'] = $this->getName();

            $fieldMap[$fieldName] = newField($fieldConfig);
        }

        return $fieldMap;
    }
}
