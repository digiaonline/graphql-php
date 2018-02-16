<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseKindTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\DocumentNode;

class DocumentNodeBuilder extends AbstractNodeBuilder
{

    use ParseKindTrait;
    use ParseLocationTrait;

    /**
     * @param array $ast
     * @return NodeInterface
     */
    public function build(array $ast): NodeInterface
    {
        return new DocumentNode([
            'kind'        => $this->parseKind($ast),
            'definitions' => $this->parseDefinitions($ast),
            'loc'         => $this->parseLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return KindEnum::DOCUMENT === $kind;
    }

    /**
     * @param array $ast
     * @return array|DefinitionNodeInterface[]
     */
    protected function parseDefinitions(array $ast): array
    {
        $definitions = [];

        if (isset($ast['definitions'])) {
            foreach ($ast['definitions'] as $definitionAst) {
                $definitions[] = $this->factory->build($definitionAst);
            }
        }

        return $definitions;
    }
}
