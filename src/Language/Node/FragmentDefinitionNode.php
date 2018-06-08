<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class FragmentDefinitionNode extends AbstractNode implements ExecutableDefinitionNodeInterface,
    DirectivesAwareInterface, NameAwareInterface
{
    use NameTrait;
    use VariableDefinitionsTrait;
    use TypeConditionTrait;
    use DirectivesTrait;
    use SelectionSetTrait;

    /**
     * FragmentDefinitionNode constructor.
     *
     * @param NameNode                 $name
     * @param VariableDefinitionNode[] $variableDefinitions
     * @param NamedTypeNode|null       $typeCondition
     * @param DirectiveNode[]          $directives
     * @param SelectionSetNode|null    $selectionSet
     * @param Location|null            $location
     */
    public function __construct(
        NameNode $name,
        array $variableDefinitions,
        ?NamedTypeNode $typeCondition,
        array $directives,
        ?SelectionSetNode $selectionSet,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::FRAGMENT_DEFINITION, $location);

        $this->name                = $name;
        $this->variableDefinitions = $variableDefinitions;
        $this->typeCondition       = $typeCondition;
        $this->directives          = $directives;
        $this->selectionSet        = $selectionSet;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'                => $this->kind,
            'name'                => $this->getNameAsArray(),
            'variableDefinitions' => $this->getVariableDefinitionsAsArray(),
            'typeCondition'       => $this->getTypeConditionAsArray(),
            'directives'          => $this->getDirectivesAsArray(),
            'selectionSet'        => $this->getSelectionSetAsArray(),
            'loc'                 => $this->getLocationAsArray(),
        ];
    }
}
