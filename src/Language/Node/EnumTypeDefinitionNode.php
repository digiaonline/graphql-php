<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class EnumTypeDefinitionNode extends AbstractNode implements TypeDefinitionNodeInterface, DirectivesAwareInterface,
    NameAwareInterface
{
    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;
    use EnumValuesTrait;

    /**
     * EnumTypeDefinitionNode constructor.
     *
     * @param StringValueNode|null      $description
     * @param NameNode                  $name
     * @param DirectiveNode[]           $directives
     * @param EnumValueDefinitionNode[] $values
     * @param Location|null             $location
     */
    public function __construct(
        ?StringValueNode $description,
        NameNode $name,
        array $directives,
        array $values,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::ENUM_TYPE_DEFINITION, $location);

        $this->description = $description;
        $this->name        = $name;
        $this->directives  = $directives;
        $this->values      = $values;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'        => $this->kind,
            'description' => $this->getDescriptionAST(),
            'name'        => $this->getNameAST(),
            'directives'  => $this->getDirectivesAST(),
            'values'      => $this->getValuesAST(),
            'loc'         => $this->getLocationAST(),
        ];
    }
}
