<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\UnionTypeDefinitionNode;
use Digia\GraphQL\Schema\Definition;
use GraphQL\Contracts\TypeSystem\Type\NamedTypeInterface;
use GraphQL\Contracts\TypeSystem\Type\ObjectTypeInterface;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;
use GraphQL\Contracts\TypeSystem\Type\UnionTypeInterface;
use function Digia\GraphQL\Type\resolveThunk;

/**
 * Union Type Definition
 *
 * When a field can return one of a heterogeneous set of types, a Union type
 * is used to describe what types are possible as well as providing a function
 * to determine which type is actually used when the field is resolved.
 *
 * Example:
 *
 *     $PetType = newUnionType([
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
class UnionType extends Definition implements UnionTypeInterface, AbstractTypeInterface, ASTNodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use ResolveTypeTrait;
    use ASTNodeTrait;

    /**
     * Types can be defined either as an array or as a thunk.
     * Using thunks allows for cross-referencing of types.
     *
     * @var array|callable
     */
    protected $typesOrThunk;

    /**
     * A key-value map over type names and their corresponding type instances.
     *
     * @var TypeInterface[]
     */
    protected $typeMap;

    /**
     * UnionType constructor.
     *
     * @param string                       $name
     * @param null|string                  $description
     * @param array|callable               $typesOrThunk
     * @param callable|null                $resolveTypeCallback
     * @param UnionTypeDefinitionNode|null $astNode
     */
    public function __construct(
        string $name,
        ?string $description,
        $typesOrThunk,
        ?callable $resolveTypeCallback,
        ?UnionTypeDefinitionNode $astNode
    ) {
        $this->name                = $name;
        $this->description         = $description;
        $this->typesOrThunk        = $typesOrThunk;
        $this->resolveTypeCallback = $resolveTypeCallback;
        $this->astNode             = $astNode;
    }

    /**
     * @return ObjectTypeInterface[]
     * @throws InvariantException
     */
    public function getTypes(): array
    {
        // Types are built lazily to avoid concurrency issues.
        if (!isset($this->typeMap)) {
            $this->typeMap = $this->buildTypeMap($this->typesOrThunk);
        }

        return $this->typeMap;
    }

    /**
     * @param string $name
     * @return ObjectTypeInterface|null
     * @throws InvariantException
     */
    public function getType(string $name): ?ObjectTypeInterface
    {
        return $this->getTypes()[$name] ?? null;
    }

    /**
     * @param string $name
     * @return bool
     * @throws InvariantException
     */
    public function hasType(string $name): bool
    {
        return $this->getType($name) !== null;
    }

    /**
     * @param array|callable $typesOrThunk
     * @return array
     * @throws InvariantException
     */
    protected function buildTypeMap($typesOrThunk): array
    {
        $typeMap = resolveThunk($typesOrThunk);

        if (!\is_array($typeMap)) {
            throw new InvariantException(\sprintf(
                'Must provide array of types or a function which returns such an array for Union %s.',
                $this->name
            ));
        }

        return $typeMap;
    }
}
