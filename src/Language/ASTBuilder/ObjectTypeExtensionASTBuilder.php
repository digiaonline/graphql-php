<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class ObjectTypeExtensionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::OBJECT_TYPE_EXTENSION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::TYPE);

        $name       = $this->buildAST(ASTKindEnum::NAME, $lexer);
        $interfaces = $this->buildAST(ASTKindEnum::IMPLEMENTS_INTERFACES, $lexer);
        $directives = $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer);
        $fields     = $this->buildAST(ASTKindEnum::FIELDS_DEFINITION, $lexer);;

        if (count($interfaces) === 0 && count($directives) === 0 && count($fields) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::OBJECT_TYPE_EXTENSION,
            'name'       => $name,
            'interfaces' => $interfaces,
            'directives' => $directives,
            'fields'     => $fields,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }
}
