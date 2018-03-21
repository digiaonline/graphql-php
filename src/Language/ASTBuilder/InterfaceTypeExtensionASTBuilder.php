<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class InterfaceTypeExtensionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::INTERFACE_TYPE_EXTENSION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::INTERFACE);

        $name       = $this->buildAST(ASTKindEnum::NAME, $lexer);
        $directives = $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer);
        $fields     = $this->buildAST(ASTKindEnum::FIELDS_DEFINITION, $lexer);

        if (count($directives) === 0 && count($fields) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::INTERFACE_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'fields'     => $fields,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }
}
