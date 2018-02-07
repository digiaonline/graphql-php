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

use Digia\GraphQL\Language\AST\NodeTrait;
use Digia\GraphQL\Language\AST\Node\InputObjectTypeDefinitionNode;

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
     * @var InputField[]
     */
    private $fields = [];

    /**
     * @return InputField[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param InputField $field
     */
    protected function addField(InputField $field): void
    {
        $this->fields[] = $field;
    }

    /**
     * @param InputField[] $fields
     */
    protected function setFields(array $fields): void
    {
        array_map(function ($config) {
            $this->addField(new InputField($config));
        }, $fields);
    }
}
