<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Schema\Definition;
use GraphQL\Contracts\TypeSystem\Type\InterfaceTypeInterface;

/**
 * Interface Type Definition
 *
 * When a field can return one of a heterogeneous set of types, a Interface type
 * is used to describe what types are possible, what fields are in common across
 * all types, as well as a function to determine which type is actually used
 * when the field is resolved.
 *
 * Example:
 *     $EntityType = newInterfaceType([
 *       'name' => 'Entity',
 *       'fields' => [
 *         'name' => ['type' => stringType()]
 *       ]
 *     ]);
 */
class InterfaceType extends Definition implements
    InterfaceTypeInterface,
    AbstractTypeInterface,
    FieldsAwareInterface,
    ASTNodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use FieldsTrait;
    use ResolveTypeTrait;
    use ASTNodeTrait;
    use ExtensionASTNodesTrait;

    /**
     * InterfaceType constructor.
     *
     * @param string                           $name
     * @param null|string                      $description
     * @param array|callable                   $rawFieldsOrThunk
     * @param callable|null                    $resolveTypeCallback
     * @param InterfaceTypeDefinitionNode|null $astNode
     * @param InterfaceTypeExtensionNode[]     $extensionASTNodes
     */
    public function __construct(
        string $name,
        ?string $description,
        $rawFieldsOrThunk,
        ?callable $resolveTypeCallback,
        ?InterfaceTypeDefinitionNode $astNode,
        array $extensionASTNodes
    ) {
        $this->name                = $name;
        $this->description         = $description;
        $this->rawFieldsOrThunk    = $rawFieldsOrThunk;
        $this->resolveTypeCallback = $resolveTypeCallback;
        $this->astNode             = $astNode;
        $this->extensionAstNodes   = $extensionASTNodes;
    }
}
