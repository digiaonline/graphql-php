<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class InputValueDefinitionNode extends AbstractNode implements DefinitionNodeInterface, DirectivesAwareInterface,
    NameAwareInterface
{
    use DescriptionTrait;
    use NameTrait;
    use TypeTrait;
    use DefaultValueTrait;
    use DirectivesTrait;

    /**
     * InputValueDefinitionNode constructor.
     *
     * @param StringValueNode|null    $description
     * @param NameNode                $name
     * @param TypeNodeInterface       $type
     * @param ValueNodeInterface|null $defaultValue
     * @param DirectiveNode[]         $directives
     * @param Location|null           $location
     */
    public function __construct(
        ?StringValueNode $description,
        NameNode $name,
        TypeNodeInterface $type,
        ?ValueNodeInterface $defaultValue,
        array $directives,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::INPUT_VALUE_DEFINITION, $location);

        $this->description  = $description;
        $this->name         = $name;
        $this->type         = $type;
        $this->defaultValue = $defaultValue;
        $this->directives   = $directives;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'         => $this->kind,
            'description'  => $this->getDescriptionAsArray(),
            'name'         => $this->getNameAsArray(),
            'type'         => $this->getTypeAsArray(),
            'defaultValue' => $this->getDefaultValueAsArray(),
            'directives'   => $this->getDirectivesAsArray(),
            'loc'          => $this->getLocationAsArray(),
        ];
    }
}
