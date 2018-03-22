<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class TypeReferenceASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::TYPE_REFERENCE;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        return $this->parseTypeReference($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseTypeReference(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        if ($this->skip($lexer, TokenKindEnum::BRACKET_L)) {
            $type = $this->parseTypeReference($lexer);

            $this->expect($lexer, TokenKindEnum::BRACKET_R);

            $type = [
                'kind' => NodeKindEnum::LIST_TYPE,
                'type' => $type,
                'loc'  => $this->buildLocation($lexer, $start),
            ];
        } else {
            $type = $this->buildAST(ASTKindEnum::NAMED_TYPE, $lexer);
        }

        if ($this->skip($lexer, TokenKindEnum::BANG)) {
            return [
                'kind' => NodeKindEnum::NON_NULL_TYPE,
                'type' => $type,
                'loc'  => $this->buildLocation($lexer, $start),
            ];
        }

        return $type;
    }
}
