<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\SerializationInterface;
use Digia\GraphQL\Language\AST\Node\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\SelectionSetTrait;
use Digia\GraphQL\Language\AST\Node\VariableDefinitionsTrait;
use Digia\GraphQL\Language\AST\Node\ExecutableDefinitionNodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class OperationDefinitionNode extends AbstractNode implements ExecutableDefinitionNodeInterface
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
