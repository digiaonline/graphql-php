<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseArgumentsTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseDirectivesTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseNameTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseSelectionSetTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class FieldBuilder extends AbstractBuilder
{

    use ParseNameTrait;
    use ParseArgumentsTrait;
    use ParseDirectivesTrait;
    use ParseSelectionSetTrait;
    use ParseLocationTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FieldNode([
            'alias'        => $this->parseName($ast, 'alias'),
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
        return $kind === NodeKindEnum::FIELD;
    }
}
