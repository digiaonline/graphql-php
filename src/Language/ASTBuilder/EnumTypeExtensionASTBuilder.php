<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class EnumTypeExtensionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::ENUM_TYPE_EXTENSION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::ENUM);

        $name       = $this->buildAST(ASTKindEnum::NAME, $lexer);
        $directives = $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer);
        $values     = $this->buildAST(ASTKindEnum::ENUM_VALUES_DEFINITION, $lexer);

        if (count($directives) === 0 && count($values) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::ENUM_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'values'     => $values,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }
}
