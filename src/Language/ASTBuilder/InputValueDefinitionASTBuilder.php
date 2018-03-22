<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class InputValueDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::INPUT_VALUE_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildAST(ASTKindEnum::DESCRIPTION, $lexer);
        $name        = $this->buildAST(ASTKindEnum::NAME, $lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        return [
            'kind'         => NodeKindEnum::INPUT_VALUE_DEFINITION,
            'description'  => $description,
            'name'         => $name,
            'type'         => $this->buildAST(ASTKindEnum::TYPE_REFERENCE, $lexer),
            'defaultValue' => $this->skip($lexer, TokenKindEnum::EQUALS)
                ? $this->buildAST(ASTKindEnum::VALUE_LITERAL, $lexer, ['isConst' => true])
                : null,
            'directives'   => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer, ['isConst' => true]),
            'loc'          => $this->buildLocation($lexer, $start),
        ];
    }
}
