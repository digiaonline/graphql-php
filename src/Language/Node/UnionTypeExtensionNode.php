<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class UnionTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface, DirectivesAwareInterface
{
    use NameTrait;
    use DirectivesTrait;
    use TypesTrait;

    /**
     * UnionTypeExtensionNode constructor.
     *
     * @param NameNode        $name
     * @param DirectiveNode[] $directives
     * @param NamedTypeNode[] $types
     * @param Location|null   $location
     */
    public function __construct(NameNode $name, array $directives, array $types, ?Location $location)
    {
        parent::__construct(NodeKindEnum::UNION_TYPE_EXTENSION, $location);

        $this->name       = $name;
        $this->directives = $directives;
        $this->types      = $types;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'       => $this->kind,
            'name'       => $this->getNameAsArray(),
            'directives' => $this->getDirectivesAsArray(),
            'types'      => $this->getTypesAsArray(),
            'loc'        => $this->getLocationAsArray(),
        ];
    }
}
