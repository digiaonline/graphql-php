<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseDirectivesTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseKindTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseSelectionSetTrait;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\VariableDefinitionNode;

class OperationDefinitionBuilder extends AbstractBuilder
{

    use ParseKindTrait;
    use ParseDirectivesTrait;
    use ParseSelectionSetTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new OperationDefinitionNode([
            'kind'                => $this->parseKind($ast),
            'operation'           => $this->parseOperation($ast),
            'variableDefinitions' => $this->parseVariableDefinitions($ast),
            'directives'          => $this->parseDirectives($ast),
            'selectionSet'        => $this->parseSelectionSet($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === KindEnum::OPERATION_DEFINITION;
    }

    /**
     * @param array $ast
     * @return null|string
     */
    protected function parseOperation(array $ast): ?string
    {
        return $ast['operation'] ?? null;
    }

    /**
     * @param array $ast
     * @return array|VariableDefinitionNode[]
     */
    protected function parseVariableDefinitions(array $ast): array
    {
        $variableDefinitions = [];

        if (isset($ast['variableDefinitions'])) {
            foreach ($ast['variableDefinitions'] as $variableDefinitionAst) {
                $variableDefinitions[] = $this->director->build($variableDefinitionAst);
            }
        }

        return $variableDefinitions;
    }
}
