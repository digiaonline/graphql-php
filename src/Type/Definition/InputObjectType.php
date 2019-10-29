<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Schema\Definition;
use GraphQL\Contracts\TypeSystem\Type\NamedTypeInterface;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\newInputField;
use function Digia\GraphQL\Type\resolveThunk;

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
 *         'lat': ['type' => newNonNull(floatType())],
 *         'lon': ['type' => newNonNull(floatType())],
 *         'alt': ['type' => floatType(), 'defaultValue' => 0],
 *       ]
 *     ]);
 */
class InputObjectType extends Definition implements
    NamedTypeInterface,
    InputTypeInterface,
    DescriptionAwareInterface,
    ASTNodeAwareInterface
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
    protected $rawFieldsOrThunk;

    /**
     * A key-value map over field names and their corresponding field instances.
     *
     * @var InputField[]
     */
    protected $fieldMap;

    /**
     * InputObjectType constructor.
     *
     * @param string                             $name
     * @param null|string                        $description
     * @param array|callable                     $rawFieldsOrThunk
     * @param InputObjectTypeDefinitionNode|null $astNode
     */
    public function __construct(
        string $name,
        ?string $description,
        $rawFieldsOrThunk,
        ?InputObjectTypeDefinitionNode $astNode
    ) {
        $this->name             = $name;
        $this->description      = $description;
        $this->rawFieldsOrThunk = $rawFieldsOrThunk;
        $this->astNode          = $astNode;
    }

    /**
     * @param string $fieldName
     * @return InputField|null
     * @throws InvariantException
     */
    public function getField(string $fieldName): ?InputField
    {
        return $this->getFields()[$fieldName] ?? null;
    }

    /**
     * @return InputField[]
     * @throws InvariantException
     */
    public function getFields(): array
    {
        if (!isset($this->fieldMap)) {
            $this->fieldMap = $this->buildFieldMap($this->rawFieldsOrThunk);
        }

        return $this->fieldMap;
    }

    /**
     * @param array|callable $rawFieldsOrThunk
     * @return array
     * @throws InvariantException
     */
    protected function buildFieldMap($rawFieldsOrThunk): array
    {
        $rawFields = resolveThunk($rawFieldsOrThunk);

        if (!isAssocArray($rawFields)) {
            throw new InvariantException(\sprintf(
                '%s fields must be an associative array with field names as keys or a function which returns such an array.',
                $this->name
            ));
        }

        $fieldMap = [];

        foreach ($rawFields as $fieldName => $fieldConfig) {
            if (isset($fieldConfig['resolve'])) {
                throw new InvariantException(\sprintf(
                    '%s.%s field type has a resolve property, but Input Types cannot define resolvers.',
                    $this->name,
                    $fieldName
                ));
            }

            $fieldConfig['name'] = $fieldName;

            $fieldMap[$fieldName] = newInputField($fieldConfig);
        }

        return $fieldMap;
    }
}
