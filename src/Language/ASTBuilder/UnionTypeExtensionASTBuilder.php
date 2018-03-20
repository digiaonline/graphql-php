<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class UnionTypeExtensionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::UNION_TYPE_EXTENSION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::UNION);

        $name       = $this->buildAST(ASTKindEnum::NAME, $lexer);
        $directives = $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer);
        $types      = $this->buildAST(ASTKindEnum::UNION_MEMBER_TYPES, $lexer);

        if (count($directives) === 0 && count($types) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::UNION_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'types'      => $types,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }
}
