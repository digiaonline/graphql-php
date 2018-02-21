<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class DocumentBuilder extends AbstractBuilder
{

    use ParseLocationTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new DocumentNode([
            'definitions' => $this->parseDefinitions($ast),
            'loc'         => $this->parseLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::DOCUMENT;
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
                $definitions[] = $this->director->build($definitionAst);
            }
        }

        return $definitions;
    }
}
