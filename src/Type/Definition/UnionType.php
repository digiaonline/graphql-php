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
 *
 * When a field can return one of a heterogeneous set of types, a Union type
 * is used to describe what types are possible as well as providing a function
 * to determine which type is actually used when the field is resolved.
 *
 * Example:
 *
 *     $PetType = GraphQLUnionType([
 *       'name' => 'Pet',
 *       'types' => [$DogType, $CatType],
 *       'resolveType' => function ($value) {
 *         if ($value instanceof Dog) {
 *           return $DogType;
 *         }
 *         if ($value instanceof Cat) {
 *           return $CatType;
 *         }
 *       }
 *     ]);
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
    protected $typesOrThunk;

    /**
     * @var TypeInterface[]
     */
    protected $typeMap;

    /**
     * @inheritdoc
     */
    protected function afterConfig(): void
    {
        invariant(null !== $this->getName(), 'Must provide name.');
    }

    /**
     * @return NamedTypeInterface[]
     * @throws InvariantException
     */
    public function getTypes(): array
    {
        // Types are built lazily to avoid concurrency issues.
        if (!isset($this->typeMap)) {
            $this->typeMap = $this->buildTypeMap($this->typesOrThunk ?? []);
        }
        return $this->typeMap;
    }

    /**
     * Unions are created using the `ConfigAwareTrait` constructor which will automatically
     * call this method when setting arguments from `$config['types']`.
     *
     * @param array|callable $typesOrThunk
     * @return UnionType
     */
    protected function setTypes($typesOrThunk): UnionType
    {
        $this->typesOrThunk = $typesOrThunk;
        return $this;
    }

    /**
     * @param array|callable $typesOrThunk
     * @return array
     * @throws InvariantException
     */
    protected function buildTypeMap($typesOrThunk): array
    {
        $typeMap = resolveThunk($typesOrThunk);

        invariant(
            \is_array($typeMap),
            \sprintf(
                'Must provide Array of types or a function which returns such an array for Union %s.',
                $this->getName()
            )
        );

        return $typeMap;
    }
}
