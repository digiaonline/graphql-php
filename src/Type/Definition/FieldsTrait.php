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
     * @var array|callable
     */
    protected $fieldsOrThunk;

    /**
     * @var Field[]
     */
    protected $fieldMap;

    /**
     * @return Field[]
     * @throws InvariantException
     */
    public function getFields(): array
    {
        // Fields are built lazily to avoid concurrency issues.
        if (!isset($this->fieldMap)) {
            $this->fieldMap = $this->buildFieldMap($this->fieldsOrThunk ?? []);
        }
        return $this->fieldMap;
    }

    /**
     * Fields are created using the `ConfigAwareTrait` constructor which will automatically
     * call this method when setting arguments from `$config['fields']`.
     *
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
                $this->getName()
            )
        );

        $fieldMap = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            invariant(
                \is_array($fieldConfig),
                \sprintf('%s.%s field config must be an array', $this->getName(), $fieldName)
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
                        '%s.%s field resolver must be a function if provided, but got: %s',
                        $this->getName(),
                        $fieldName,
                        toString($fieldConfig['resolve'])
                    )
                );
            }

            $fieldConfig['name'] = $fieldName;
            $fieldMap[$fieldName] = new Field($fieldConfig);
        }

        return $fieldMap;
    }
}
