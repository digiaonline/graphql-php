<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class EnumTypeDefinitionNode extends AbstractNode implements TypeDefinitionNodeInterface, DirectivesAwareInterface
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
    public function toArray(): array
    {
        return [
            'kind'        => $this->kind,
            'description' => $this->getDescriptionAsArray(),
            'name'        => $this->getNameAsArray(),
            'directives'  => $this->getDirectivesAsArray(),
            'values'      => $this->getValuesAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }
}
