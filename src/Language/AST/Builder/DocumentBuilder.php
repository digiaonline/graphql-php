<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class DocumentBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new DocumentNode([
            'definitions' => $this->buildMany($ast, 'definitions'),
            'location'    => $this->createLocation($ast),
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
