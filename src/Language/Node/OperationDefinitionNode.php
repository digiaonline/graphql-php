<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class OperationDefinitionNode extends AbstractNode implements ExecutableDefinitionNodeInterface,
    DirectivesAwareInterface, NameAwareInterface, SelectionSetAwareInterface
{
    use NameTrait;
    use DirectivesTrait;
    use VariableDefinitionsTrait;
    use SelectionSetTrait;

    /**
     * @var string
     */
    protected $operation;

    /**
     * OperationDefinitionNode constructor.
     *
     * @param string                   $operation
     * @param NameNode|null            $name
     * @param VariableDefinitionNode[] $variableDefinitions
     * @param DirectiveNode[]          $directives
     * @param SelectionSetNode|null    $selectionSet
     * @param Location|null            $location
     */
    public function __construct(
        string $operation,
        ?NameNode $name,
        array $variableDefinitions,
        array $directives,
        ?SelectionSetNode $selectionSet,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::OPERATION_DEFINITION, $location);

        $this->operation           = $operation;
        $this->name                = $name;
        $this->variableDefinitions = $variableDefinitions;
        $this->directives          = $directives;
        $this->selectionSet        = $selectionSet;
    }

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
    public function toAST(): array
    {
        return [
            'kind'                => $this->kind,
            'loc'                 => $this->getLocationAST(),
            'operation'           => $this->operation,
            'name'                => $this->getNameAST(),
            'variableDefinitions' => $this->getVariableDefinitionsAST(),
            'directives'          => $this->getDirectivesAST(),
            'selectionSet'        => $this->getSelectionSetAST(),
        ];
    }
}
