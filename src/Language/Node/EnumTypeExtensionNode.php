<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class EnumTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface, DirectivesAwareInterface,
    NameAwareInterface
{
    use NameTrait;
    use DirectivesTrait;
    use EnumValuesTrait;

    /**
     * EnumTypeExtensionNode constructor.
     *
     * @param NameNode                  $name
     * @param DirectiveNode[]           $directives
     * @param EnumValueDefinitionNode[] $values
     * @param Location|null             $location
     */
    public function __construct(NameNode $name, array $directives, array $values, ?Location $location)
    {
        parent::__construct(NodeKindEnum::ENUM_TYPE_EXTENSION, $location);

        $this->name       = $name;
        $this->directives = $directives;
        $this->values     = $values;
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
            'values'     => $this->getValuesAST(),
            'loc'        => $this->getLocationAST(),
        ];
    }
}
