<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class FieldsDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::FIELDS_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'parseFieldDefinition'],
                TokenKindEnum::BRACE_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseFieldDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->buildAST(ASTKindEnum::DESCRIPTION, $lexer);
        $name        = $this->buildAST(ASTKindEnum::NAME, $lexer);
        $arguments   = $this->buildAST(ASTKindEnum::ARGUMENTS_DEFINITION, $lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        return [
            'kind'        => NodeKindEnum::FIELD_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'arguments'   => $arguments,
            'type'        => $this->buildAST(ASTKindEnum::TYPE_REFERENCE, $lexer),
            'directives'  => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }
}
