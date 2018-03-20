<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class SchemaDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::SCHEMA_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::SCHEMA);

        return [
            'kind'           => NodeKindEnum::SCHEMA_DEFINITION,
            'directives'     => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer),
            'operationTypes' => $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'parseOperationTypeDefinition'],
                TokenKindEnum::BRACE_R
            ),
            'loc'            => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseOperationTypeDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $operation = $this->parseOperationType($lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        return [
            'kind'      => NodeKindEnum::OPERATION_TYPE_DEFINITION,
            'operation' => $operation,
            'type'      => $this->buildAST(ASTKindEnum::NAMED_TYPE, $lexer),
            'loc'       => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return string
     * @throws SyntaxErrorException
     */
    protected function parseOperationType(LexerInterface $lexer): string
    {
        $token = $this->expect($lexer, TokenKindEnum::NAME);
        $value = $token->getValue();

        if ($this->isOperation($value)) {
            return $value;
        }

        throw $this->unexpected($lexer, $token);
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function isOperation(string $value): bool
    {
        return \in_array($value, ['query', 'mutation', 'subscription'], true);
    }
}
