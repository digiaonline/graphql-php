<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class UnionTypeDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::UNION_TYPE_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildAST(ASTKindEnum::DESCRIPTION, $lexer);

        $this->expectKeyword($lexer, KeywordEnum::UNION);

        return [
            'kind'        => NodeKindEnum::UNION_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $this->buildAST(ASTKindEnum::NAME, $lexer),
            'directives'  => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer),
            'types'       => $this->buildAST(ASTKindEnum::UNION_MEMBER_TYPES, $lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }
}
