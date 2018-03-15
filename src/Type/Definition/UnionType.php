<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;
use Digia\GraphQL\Language\Node\UnionTypeDefinitionNode;
use function Digia\GraphQL\Type\resolveThunk;
use function Digia\GraphQL\Util\invariant;

/**
 * Union Type Definition
 * When a field can return one of a heterogeneous set of types, a Union type
 * is used to describe what types are possible as well as providing a function
 * to determine which type is actually used when the field is resolved.
 * Example:
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
 */

/**
 * Class UnionType
 *
 * @package Digia\GraphQL\Type\Definition
 * @property UnionTypeDefinitionNode $astNode
 */
class UnionType extends ConfigObject implements AbstractTypeInterface, NamedTypeInterface, CompositeTypeInterface,
    OutputTypeInterface, NodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use ResolveTypeTrait;
    use NodeTrait;

    /**
     * @var array|callable
     */
    private $_typesThunk;

    /**
     * @var TypeInterface[]
     */
    private $_typeMap = [];

    /**
     * @var bool
     */
    private $_isTypesDefines = false;

    /**
     * @inheritdoc
     */
    protected function beforeConfig(): void
    {
        $this->setName(TypeNameEnum::UNION);
    }

    /**
     * @return NamedTypeInterface[]
     * @throws InvariantException
     */
    public function getTypes(): array
    {
        $this->defineTypesIfNecessary();

        return $this->_typeMap;
    }

    /**
     * @param array|callable $typesThunk
     * @return UnionType
     */
    protected function setTypes($typesThunk): UnionType
    {
        $this->_typesThunk = $typesThunk;

        return $this;
    }

    /**
     * @throws InvariantException
     */
    protected function defineTypesIfNecessary()
    {
        // Types are built lazily to avoid concurrency issues.
        if (!$this->_isTypesDefines) {
            $this->_typeMap = array_merge($this->defineTypes($this->_typesThunk), $this->_typeMap);

            $this->_isTypesDefines = true;
        }
    }

    /**
     * @param array|callable $typesThunk
     * @return array
     * @throws InvariantException
     */
    protected function defineTypes($typesThunk): array
    {
        $types = resolveThunk($typesThunk);

        invariant(
            is_array($types),
            sprintf(
                'Must provide Array of types or a function which returns such an array for Union %s.',
                $this->getName()
            )
        );

        return $types;
    }
}
