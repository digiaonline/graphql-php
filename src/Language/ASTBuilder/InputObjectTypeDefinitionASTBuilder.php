<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class InputObjectTypeDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::INPUT_OBJECT_TYPE_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildAST(ASTKindEnum::DESCRIPTION, $lexer);

        $this->expectKeyword($lexer, KeywordEnum::INPUT);

        return [
            'kind'        => NodeKindEnum::INPUT_OBJECT_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $this->buildAST(ASTKindEnum::NAME, $lexer),
            'directives'  => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer, ['isConst' => true]),
            'fields'      => $this->buildAST(ASTKindEnum::INPUT_FIELDS_DEFINITION, $lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }
}
