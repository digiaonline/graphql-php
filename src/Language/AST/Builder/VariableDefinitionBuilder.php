<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseNameTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseTypeTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseValueLiteralTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class VariableDefinitionBuilder extends AbstractBuilder
{

    use ParseNameTrait;
    use ParseTypeTrait;
    use ParseValueLiteralTrait;
    use ParseLocationTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new VariableDefinitionNode([
            'variable'     => $this->parseVariable($ast),
            'type'         => $this->parseType($ast),
            'defaultValue' => $this->parseValueLiteral($ast, 'defaultValue'),
            'loc'          => $this->parseLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::VARIABLE_DEFINITION;
    }

    /**
     * @param array $ast
     * @return NodeInterface
     */
    protected function parseVariable(array $ast): NodeInterface
    {
        return $this->getDirector()->build($ast['variable']);
    }
}
