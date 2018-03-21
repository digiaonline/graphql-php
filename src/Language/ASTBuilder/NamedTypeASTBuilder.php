<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class NamedTypeASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::NAMED_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        return [
            'kind' => NodeKindEnum::NAMED_TYPE,
            'name' => $this->buildAST(ASTKindEnum::NAME, $lexer),
            'loc'  => $this->buildLocation($lexer, $start),
        ];
    }
}
