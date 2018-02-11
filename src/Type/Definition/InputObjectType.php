<?php

namespace Digia\GraphQL\Type\Definition;

/**
 * Input Object Type Definition
 * An input object defines a structured collection of fields which may be
 * supplied to a field argument.
 * Using `NonNull` will ensure that a value must be provided by the query
 * Example:
 *     const GeoPoint = new GraphQLInputObjectType({
 *       name: 'GeoPoint',
 *       fields: {
 *         lat: { type: GraphQLNonNull(GraphQLFloat) },
 *         lon: { type: GraphQLNonNull(GraphQLFloat) },
 *         alt: { type: GraphQLFloat, defaultValue: 0 },
 *       }
 *     });
 */

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Type\Definition\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Type\Definition\Behavior\NameTrait;
use Digia\GraphQL\Type\Definition\Contract\InputTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\resolveThunk;
use function Digia\GraphQL\Util\invariant;

/**
 * Class InputObjectType
 *
 * @package Digia\GraphQL\Type\Definition
 * @property InputObjectTypeDefinitionNode $astNode
 */
class InputObjectType implements TypeInterface, InputTypeInterface
{

    use NameTrait;
    use DescriptionTrait;
    use NodeTrait;
    use ConfigTrait;

    /**
     * @var array|callable
     */
    private $_fieldsThunk;

    /**
     * @var null|InputField[]
     */
    private $_fieldMap = [];

    /**
     * @var bool
     */
    private $_isFieldMapBuilt = false;

    /**
     * @return InputField[]
     * @throws \Exception
     */
    public function getFields(): array
    {
        $this->buildFieldMapIfNecessary();

        return $this->_fieldMap;
    }

    /**
     * @param InputField $field
     * @return $this
     * @throws \Exception
     */
    protected function addField(InputField $field)
    {
        $this->_fieldMap[$field->getName()] = $field;

        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     * @throws \Exception
     */
    protected function addFields(array $fields)
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
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
     * @throws \Exception
     */
    protected function buildFieldMapIfNecessary()
    {
        if (!$this->_isFieldMapBuilt) {
            $this->_fieldMap = array_merge($this->defineFieldMap($this->_fieldsThunk), $this->_fieldMap);

            $this->_isFieldMapBuilt = true;
        }
    }

    /**
     * @param array|callable $fieldsThunk
     * @return array
     * @throws \Exception
     */
    protected function defineFieldMap($fieldsThunk): array
    {
        $fields = resolveThunk($fieldsThunk) ?: [];

        invariant(
            isAssocArray($fields),
            sprintf(
                '%s fields must be an associative array with field names as keys or a function which returns such an array.',
                $this->getName()
            )
        );

        $fieldMap = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            invariant(
                !isset($fieldConfig['resolve']),
                sprintf(
                    '%s.%s field type has a resolve property, but Input Types cannot define resolvers.',
                    $this->getName(),
                    $fieldName
                )
            );

            $fieldMap[$fieldName] = new InputField(array_merge($fieldConfig, ['name' => $fieldName]));
        }

        return $fieldMap;
    }
}
