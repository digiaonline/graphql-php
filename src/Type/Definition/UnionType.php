<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\NodeTrait;
use Digia\GraphQL\Language\AST\Node\UnionTypeDefinitionNode;


/**
 * Union Type Definition
 *
 * When a field can return one of a heterogeneous set of types, a Union type
 * is used to describe what types are possible as well as providing a function
 * to determine which type is actually used when the field is resolved.
 *
 * Example:
 *
 *     const PetType = new GraphQLUnionType({
 *       name: 'Pet',
 *       types: [ DogType, CatType ],
 *       resolveType(value) {
 *         if (value instanceof Dog) {
 *           return DogType;
 *         }
 *         if (value instanceof Cat) {
 *           return CatType;
 *         }
 *       }
 *     });
 *
 */

/**
 * Class UnionType
 *
 * @package Digia\GraphQL\Type\Definition
 * @property UnionTypeDefinitionNode $astNode
 */
class UnionType implements AbstractTypeInterface, CompositeTypeInterface, OutputTypeInterface
{

    use NameTrait;
    use DescriptionTrait;
    use ResolveTypeTrait;
    use NodeTrait;
    use ConfigTrait;

    /**
     * @var TypeInterface[]
     */
    private $types;

    /**
     * @return TypeInterface[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param TypeInterface $type
     */
    public function addType(TypeInterface $type): void
    {
        $this->types[] = $type;
    }

    /**
     * @param array $types
     */
    protected function setTypes(array $types): void
    {
        array_map(function ($type) {
            $this->addType($type);
        }, $types);
    }
}
