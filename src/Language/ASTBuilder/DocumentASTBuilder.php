<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class DocumentASTBuilder extends AbstractASTBuilder
{
    /**
     * @param LexerInterface $lexer
     * @return bool
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::DOCUMENT;
    }

    /**
     * @inheritdoc
     * @throws SyntaxErrorException
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::SOF);

        $definitions = [];

        do {
            $definitions[] = $this->parseDefinition($lexer);
        } while (!$this->skip($lexer, TokenKindEnum::EOF));

        return [
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => $definitions,
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function parseDefinition(LexerInterface $lexer): array
    {
        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            switch ($lexer->getTokenValue()) {
                case KeywordEnum::QUERY:
                case KeywordEnum::MUTATION:
                case KeywordEnum::SUBSCRIPTION:
                case KeywordEnum::FRAGMENT:
                    return $this->parseExecutableDefinition($lexer);
                case KeywordEnum::SCHEMA:
                case KeywordEnum::SCALAR:
                case KeywordEnum::TYPE:
                case KeywordEnum::INTERFACE:
                case KeywordEnum::UNION:
                case KeywordEnum::ENUM:
                case KeywordEnum::INPUT:
                case KeywordEnum::EXTEND:
                case KeywordEnum::DIRECTIVE:
                    // Note: The schema definition language is an experimental addition.
                    return $this->parseTypeSystemDefinition($lexer);
            }
        } elseif ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            return $this->parseExecutableDefinition($lexer);
        } elseif ($this->peekDescription($lexer)) {
            // Note: The schema definition language is an experimental addition.
            return $this->parseTypeSystemDefinition($lexer);
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseExecutableDefinition(LexerInterface $lexer): array
    {
        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            // valid names are: query, mutation, subscription and fragment
            switch ($lexer->getToken()->getValue()) {
                case KeywordEnum::QUERY:
                case KeywordEnum::MUTATION:
                case KeywordEnum::SUBSCRIPTION:
                    return $this->buildAST(ASTKindEnum::OPERATION_DEFINITION, $lexer);
                case KeywordEnum::FRAGMENT:
                    return $this->buildAST(ASTKindEnum::FRAGMENT_DEFINITION, $lexer);
            }
        } elseif ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            // Anonymous query
            return $this->buildAST(ASTKindEnum::OPERATION_DEFINITION, $lexer);
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseTypeSystemDefinition(LexerInterface $lexer): array
    {
        // Many definitions begin with a description and require a lookahead.
        $keywordToken = $this->peekDescription($lexer) ? $lexer->lookahead() : $lexer->getToken();

        if ($keywordToken->getKind() === TokenKindEnum::NAME) {
            switch ($keywordToken->getValue()) {
                case KeywordEnum::SCHEMA:
                    return $this->buildAST(ASTKindEnum::SCHEMA_DEFINITION, $lexer);
                case KeywordEnum::SCALAR:
                    return $this->buildAST(ASTKindEnum::SCALAR_TYPE_DEFINITION, $lexer);
                case KeywordEnum::TYPE:
                    return $this->buildAST(ASTKindEnum::OBJECT_TYPE_DEFINITION, $lexer);
                case KeywordEnum::INTERFACE:
                    return $this->buildAST(ASTKindEnum::INTERFACE_TYPE_DEFINITION, $lexer);
                case KeywordEnum::UNION:
                    return $this->buildAST(ASTKindEnum::UNION_TYPE_DEFINITION, $lexer);
                case KeywordEnum::ENUM:
                    return $this->buildAST(ASTKindEnum::ENUM_TYPE_DEFINITION, $lexer);
                case KeywordEnum::INPUT:
                    return $this->buildAST(ASTKindEnum::INPUT_OBJECT_TYPE_DEFINITION, $lexer);
                case KeywordEnum::DIRECTIVE:
                    return $this->buildAST(ASTKindEnum::DIRECTIVE_DEFINITION, $lexer);
                case KeywordEnum::EXTEND:
                    return $this->parseTypeSystemExtension($lexer);
            }
        }

        throw $this->unexpected($lexer, $keywordToken);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseTypeSystemExtension(LexerInterface $lexer): array
    {
        $keywordToken = $lexer->lookahead();

        if ($keywordToken->getKind() === TokenKindEnum::NAME) {
            switch ($keywordToken->getValue()) {
                case KeywordEnum::SCALAR:
                    return $this->buildAST(ASTKindEnum::SCALAR_TYPE_EXTENSION, $lexer);
                case KeywordEnum::TYPE:
                    return $this->buildAST(ASTKindEnum::OBJECT_TYPE_EXTENSION, $lexer);
                case KeywordEnum::INTERFACE:
                    return $this->buildAST(ASTKindEnum::INTERFACE_TYPE_EXTENSION, $lexer);
                case KeywordEnum::UNION:
                    return $this->buildAST(ASTKindEnum::UNION_TYPE_EXTENSION, $lexer);
                case KeywordEnum::ENUM:
                    return $this->buildAST(ASTKindEnum::ENUM_TYPE_EXTENSION, $lexer);
                case KeywordEnum::INPUT:
                    return $this->buildAST(ASTKindEnum::INPUT_OBJECT_TYPE_EXTENSION, $lexer);
            }
        }

        throw $this->unexpected($lexer, $keywordToken);
    }
}
