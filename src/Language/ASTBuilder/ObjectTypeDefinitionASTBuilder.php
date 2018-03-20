<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class ObjectTypeDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::OBJECT_TYPE_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildAST(ASTKindEnum::DESCRIPTION, $lexer);

        $this->expectKeyword($lexer, KeywordEnum::TYPE);

        return [
            'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $this->buildAST(ASTKindEnum::NAME, $lexer),
            'interfaces'  => $this->buildAST(ASTKindEnum::IMPLEMENTS_INTERFACES, $lexer),
            'directives'  => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer),
            'fields'      => $this->buildAST(ASTKindEnum::FIELDS_DEFINITION, $lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }
}
