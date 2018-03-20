<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class ScalarTypeExtensionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::SCALAR_TYPE_EXTENSION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::SCALAR);

        $name       = $this->buildAST(ASTKindEnum::NAME, $lexer);
        $directives = $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer);

        if (count($directives) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::SCALAR_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }
}
