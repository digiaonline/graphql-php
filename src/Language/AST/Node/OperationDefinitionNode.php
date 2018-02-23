<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Contract\SerializationInterface;
use Digia\GraphQL\Language\AST\Node\Behavior\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\SelectionSetTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\VariableDefinitionsTrait;
use Digia\GraphQL\Language\AST\Node\Contract\ExecutableDefinitionNodeInterface;
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
