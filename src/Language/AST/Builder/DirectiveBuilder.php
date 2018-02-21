<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseArgumentsTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseKindTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseNameTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseValueTrait;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\DirectiveNode;

class DirectiveBuilder extends AbstractBuilder
{

    use ParseKindTrait;
    use ParseNameTrait;
    use ParseValueTrait;
    use ParseArgumentsTrait;
    use ParseLocationTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new DirectiveNode([
            'kind'      => $this->parseKind($ast),
            'name'      => $this->parseName($ast),
            'arguments' => $this->parseArguments($ast),
            'loc'       => $this->parseLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::DIRECTIVE;
    }
}
