<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class ScalarTypeExtensionNode extends AbstractNode implements
    TypeSystemExtensionNodeInterface,
    NameAwareInterface,
    DirectivesAwareInterface
{
    use NameTrait;
    use DirectivesTrait;

    /**
     * ScalarTypeDefinitionNode constructor.
     *
     * @param NameNode        $name
     * @param DirectiveNode[] $directives
     * @param Location|null   $location
     */
    public function __construct(NameNode $name, array $directives, ?Location $location)
    {
        parent::__construct(NodeKindEnum::SCALAR_TYPE_EXTENSION, $location);

        $this->name       = $name;
        $this->directives = $directives;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'       => $this->kind,
            'name'       => $this->getNameAST(),
            'directives' => $this->getDirectivesAST(),
            'loc'        => $this->getLocationAST(),
        ];
    }
}
