<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseKindTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\ValueNodeInterface;
use Digia\GraphQL\Language\AST\Node\ListValueNode;

class ListBuilder extends AbstractBuilder
{

    use ParseKindTrait;
    use ParseLocationTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new ListValueNode([
            'kind'   => $this->parseKind($ast),
            'values' => $this->parseValues($ast),
            'loc'    => $this->parseLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::LIST;
    }

    /**
     * @param array $ast
     * @return array|ValueNodeInterface[]
     */
    protected function parseValues(array $ast): array
    {
        $values = [];

        if (isset($ast['values'])) {
            foreach ($ast['values'] as $valueAst) {
                $values[] = $this->getDirector()->build($valueAst);
            }
        }

        return $values;
    }
}
