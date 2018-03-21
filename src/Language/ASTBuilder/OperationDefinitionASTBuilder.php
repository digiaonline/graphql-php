<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class OperationDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::OPERATION_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        if ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            return [
                'kind'                => NodeKindEnum::OPERATION_DEFINITION,
                'operation'           => 'query',
                'name'                => null,
                'variableDefinitions' => [],
                'directives'          => [],
                'selectionSet'        => $this->buildAST(ASTKindEnum::SELECTION_SET, $lexer),
                'loc'                 => $this->buildLocation($lexer, $start),
            ];
        }

        $operation = $this->parseOperationType($lexer);

        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            $name = $this->buildAST(ASTKindEnum::NAME, $lexer);
        }

        return [
            'kind'                => NodeKindEnum::OPERATION_DEFINITION,
            'operation'           => $operation,
            'name'                => $name ?? null,
            'variableDefinitions' => $this->buildAST(ASTKindEnum::VARIABLE_DEFINITION, $lexer),
            'directives'          => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer, ['isConst' => false]),
            'selectionSet'        => $this->buildAST(ASTKindEnum::SELECTION_SET, $lexer),
            'loc'                 => $this->buildLocation($lexer, $start),
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
