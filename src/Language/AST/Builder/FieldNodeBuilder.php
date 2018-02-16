<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseAliasTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseArgumentsTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseDirectivesTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseKindTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseNameTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseSelectionSetTrait;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\FieldNode;

class FieldNodeBuilder extends AbstractNodeBuilder
{

    use ParseKindTrait;
    use ParseNameTrait;
    use ParseAliasTrait;
    use ParseArgumentsTrait;
    use ParseDirectivesTrait;
    use ParseSelectionSetTrait;
    use ParseLocationTrait;

    /**
     * @param array $ast
     * @return NodeInterface
     */
    public function build(array $ast): NodeInterface
    {
        return new FieldNode([
            'kind'         => $this->parseKind($ast),
            'alias'        => $this->parseAlias($ast),
            'name'         => $this->parseName($ast),
            'arguments'    => $this->parseArguments($ast),
            'directives'   => $this->parseDirectives($ast),
            'selectionSet' => $this->parseSelectionSet($ast),
            'loc'          => $this->parseLocation($ast),
        ]);
    }

    /**
     * @param string $kind
     * @return bool
     */
    public function supportsKind(string $kind): bool
    {
        return KindEnum::FIELD === $kind;
    }
}
