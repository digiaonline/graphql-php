<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseKindTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseValueTrait;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\ObjectValueNode;

class ObjectBuilder extends AbstractBuilder
{

    use ParseKindTrait;
    use ParseValueTrait;
    use ParseLocationTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new ObjectValueNode([
            'kind'   => $this->parseKind($ast),
            'fields' => $this->parseFields($ast),
            'loc'    => $this->parseLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === KindEnum::OBJECT;
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
