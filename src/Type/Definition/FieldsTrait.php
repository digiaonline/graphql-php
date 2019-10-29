<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use GraphQL\Contracts\TypeSystem\FieldInterface;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\newField;
use function Digia\GraphQL\Type\resolveThunk;
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
     * @param string $fieldName
     * @return Field|FieldInterface|null
     * @throws InvariantException
     */
    public function getField(string $fieldName): ?FieldInterface
    {
        return $this->getFields()[$fieldName] ?? null;
    }

    /**
     * @param string $fieldName
     * @return bool
     * @throws InvariantException
     */
    public function hasField(string $fieldName): bool
    {
        return $this->getField($fieldName) !== null;
    }

    /**
     * @return Field[]
     * @throws InvariantException
     */
    public function getFields(): iterable
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

        if (!isAssocArray($rawFields)) {
            throw new InvariantException(\sprintf(
                '%s fields must be an associative array with field names as keys or a ' .
                'callable which returns such an array.',
                $this->getName()
            ));
        }

        $fieldMap = [];

        foreach ($rawFields as $fieldName => $fieldConfig) {
            if (!\is_array($fieldConfig)) {
                throw new InvariantException(\sprintf('%s.%s field config must be an associative array.',
                    $this->getName(), $fieldName));
            }

            if (isset($fieldConfig['isDeprecated'])) {
                throw new InvariantException(\sprintf(
                    '%s.%s should provide "deprecationReason" instead of "isDeprecated".',
                    $this->getName(),
                    $fieldName
                ));
            }

            if (isset($fieldConfig['resolve']) && !\is_callable($fieldConfig['resolve'])) {
                throw new InvariantException(\sprintf(
                    '%s.%s field resolver must be a function if provided, but got: %s.',
                    $this->getName(),
                    $fieldName,
                    toString($fieldConfig['resolve'])
                ));
            }

            $fieldConfig['name']     = $fieldName;
            $fieldConfig['typeName'] = $this->getName();

            $fieldMap[$fieldName] = newField($fieldConfig);
        }

        return $fieldMap;
    }
}
