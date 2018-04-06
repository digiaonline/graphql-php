<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigAwareInterface;
use Digia\GraphQL\Config\ConfigAwareTrait;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\resolveThunk;
use function Digia\GraphQL\Util\invariant;

/**
 * Input Object Type Definition
 *
 * An input object defines a structured collection of fields which may be
 * supplied to a field argument.
 *
 * Using `NonNull` will ensure that a value must be provided by the query
 *
 * Example:
 *
 *     $GeoPoint = GraphQLInputObjectType([
 *       'name': 'GeoPoint',
 *       'fields': [
 *         'lat': ['type' => GraphQLNonNull(GraphQLFloat())],
 *         'lon': ['type' => GraphQLNonNull(GraphQLFloat())],
 *         'alt': ['type' => GraphQLFloat(), 'defaultValue' => 0],
 *       ]
 *     ]);
 */
class InputObjectType implements TypeInterface, NamedTypeInterface, InputTypeInterface, ConfigAwareInterface,
    NodeAwareInterface
{

    use ConfigAwareTrait;
    use NameTrait;
    use DescriptionTrait;
    use NodeTrait;

    /**
     * @var array|callable
     */
    protected $fieldsOrThunk;

    /**
     * @var null|InputField[]
     */
    protected $fieldMap;

    /**
     * @return InputField[]
     * @throws InvariantException
     */
    public function getFields(): array
    {
        if (!isset($this->fieldMap)) {
            $this->fieldMap = $this->buildFieldMap($this->fieldsOrThunk ?? []);
        }

        return $this->fieldMap;
    }

    /**
     * @param array|callable $fieldsOrThunk
     *
     * @return array
     * @throws InvariantException
     */
    protected function buildFieldMap($fieldsOrThunk): array
    {
        $fields = resolveThunk($fieldsOrThunk) ?? [];

        invariant(
            isAssocArray($fields),
            \sprintf(
                '%s fields must be an associative array with field names as keys or a function which returns such an array.',
                $this->getName()
            )
        );

        $fieldMap = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            invariant(
                !isset($fieldConfig['resolve']),
                \sprintf(
                    '%s.%s field type has a resolve property, but Input Types cannot define resolvers.',
                    $this->getName(),
                    $fieldName
                )
            );

            $fieldConfig['name'] = $fieldName;
            $fieldMap[$fieldName] = new InputField($fieldConfig);
        }

        return $fieldMap;
    }

    /**
     * @inheritdoc
     */
    protected function afterConfig(): void
    {
        invariant(null !== $this->getName(), 'Must provide name.');
    }

    /**
     * Input fields are created using the `ConfigAwareTrait` constructor which
     * will automatically call this method when setting arguments from
     * `$config['fields']`.
     *
     * @param array|callable $fieldsOrThunk
     *
     * @return $this
     */
    protected function setFields($fieldsOrThunk)
    {
        $this->fieldsOrThunk = $fieldsOrThunk;

        return $this;
    }
}
