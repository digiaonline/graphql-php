<?php

namespace Digia\GraphQL\Language\Node;

class OperationDefinitionNode extends AbstractNode implements ExecutableDefinitionNodeInterface, DirectivesAwareInterface
{
    use NameTrait;
    use DirectivesTrait;
    use VariableDefinitionsTrait;
    use SelectionSetTrait;

    protected $kind = NodeKindEnum::OPERATION_DEFINITION;

    /**
     * @var string
     */
    protected $operation;

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'                => $this->kind,
            'loc'                 => $this->getLocationAsArray(),
            'operation'           => $this->operation,
            'name'                => $this->getNameAsArray(),
            'variableDefinitions' => $this->getVariableDefinitionsAsArray(),
            'directives'          => $this->getDirectivesAsArray(),
            'selectionSet'        => $this->getSelectionSetAsArray(),
        ];
    }
}
