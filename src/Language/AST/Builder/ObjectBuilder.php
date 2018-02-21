<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseValueTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\ObjectValueNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class ObjectBuilder extends AbstractBuilder
{

    use ParseValueTrait;
    use ParseLocationTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new ObjectValueNode([
            'fields' => $this->parseFields($ast),
            'loc'    => $this->parseLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::OBJECT;
    }

    /**
     * @param array $ast
     * @return array|FieldNode[]
     */
    protected function parseFields(array $ast): array
    {
        $fields = [];

        foreach ($ast['fields'] as $fieldAst) {
            $fields[] = $this->getDirector()->build($fieldAst);
        }

        return $fields;
    }
}
