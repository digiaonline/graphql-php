<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class UnionTypeDefinitionNode extends AbstractNode implements TypeDefinitionNodeInterface, NameAwareInterface
{
    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;
    use TypesTrait;

    /**
     * UnionTypeDefinitionNode constructor.
     *
     * @param StringValueNode|null $description
     * @param NameNode             $name
     * @param DirectiveNode[]      $directives
     * @param NamedTypeNode[]      $types
     * @param Location|null        $location
     */
    public function __construct(
        ?StringValueNode $description,
        NameNode $name,
        array $directives,
        array $types,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::UNION_TYPE_DEFINITION, $location);

        $this->description = $description;
        $this->name        = $name;
        $this->directives  = $directives;
        $this->types       = $types;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'        => $this->kind,
            'description' => $this->getDescriptionAsArray(),
            'name'        => $this->getNameAsArray(),
            'directives'  => $this->getDirectivesAsArray(),
            'types'       => $this->getTypesAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }
}
