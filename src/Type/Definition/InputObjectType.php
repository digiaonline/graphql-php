<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\InputObjectTypeDefinitionNode;
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
 *     $GeoPoint = newInputObjectType([
 *       'name': 'GeoPoint',
 *       'fields': [
 *         'lat': ['type' => newNonNull(Float())],
 *         'lon': ['type' => newNonNull(Float())],
 *         'alt': ['type' => Float(), 'defaultValue' => 0],
 *       ]
 *     ]);
 */
class InputObjectType implements TypeInterface, NamedTypeInterface, InputTypeInterface, ASTNodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use ASTNodeTrait;

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
     * @var null|InputField[]
     */
    protected $fieldMap;

    /**
     * InputObjectType constructor.
     *
     * @param string                             $name
     * @param null|string                        $description
     * @param array|callable                     $fieldsOrThunk
     * @param InputObjectTypeDefinitionNode|null $astNode
     * @throws InvariantException
     */
    public function __construct(
        string $name,
        ?string $description,
        $fieldsOrThunk,
        ?InputObjectTypeDefinitionNode $astNode
    ) {
        $this->name          = $name;
        $this->description   = $description;
        $this->fieldsOrThunk = $fieldsOrThunk;
        $this->astNode       = $astNode;

        invariant(null !== $this->name, 'Must provide name.');
    }

    /**
     * @return InputField[]
     * @throws InvariantException
     */
    public function getFields(): array
    {
        if (!isset($this->fieldMap)) {
            $this->fieldMap = $this->buildFieldMap($this->fieldsOrThunk);
        }

        return $this->fieldMap;
    }

    /**
     * @param array|callable $fieldsOrThunk
     * @return array
     * @throws InvariantException
     */
    protected function buildFieldMap($fieldsOrThunk): array
    {
        $fields = resolveThunk($fieldsOrThunk);

        invariant(
            isAssocArray($fields),
            \sprintf(
                '%s fields must be an associative array with field names as keys or a function which returns such an array.',
                $this->name
            )
        );

        $fieldMap = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            invariant(
                !isset($fieldConfig['resolve']),
                \sprintf(
                    '%s.%s field type has a resolve property, but Input Types cannot define resolvers.',
                    $this->name,
                    $fieldName
                )
            );

            $fieldMap[$fieldName] = new InputField(
                $fieldName,
                $fieldConfig['type'] ?? null,
                $fieldConfig['defaultValue'] ?? null,
                $fieldConfig['description'] ?? null,
                $fieldConfig['astNode'] ?? null
            );
        }

        return $fieldMap;
    }
}
