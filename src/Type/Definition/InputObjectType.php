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
use Digia\GraphQL\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Type\Behavior\NameTrait;
use Digia\GraphQL\Type\Contract\InputTypeInterface;
use Digia\GraphQL\Type\Contract\TypeInterface;

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
     * @return $this
     */
    protected function addField(InputField $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    protected function addFields(array $fields)
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

    /**
     * @param InputField[] $fields
     * @return $this
     */
    protected function setFields(array $fields)
    {
        $this->addFields(array_map(function ($config) {
            return new InputField($config);
        }, $fields));

        return $this;
    }
}
