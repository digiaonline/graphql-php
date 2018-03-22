<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class FragmentDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::FRAGMENT_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::FRAGMENT);

        $parseTypeCondition = function (LexerInterface $lexer) {
            $this->expectKeyword($lexer, 'on');
            return $this->buildAST(ASTKindEnum::NAMED_TYPE, $lexer);
        };

        return [
            'kind'                => NodeKindEnum::FRAGMENT_DEFINITION,
            'name'                => $this->parseFragmentName($lexer),
            'variableDefinitions' => $this->buildAST(ASTKindEnum::VARIABLE_DEFINITION, $lexer),
            'typeCondition'       => $parseTypeCondition($lexer),
            'directives'          => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer),
            'selectionSet'        => $this->buildAST(ASTKindEnum::SELECTION_SET, $lexer),
            'loc'                 => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseFragmentName(LexerInterface $lexer): array
    {
        if ($lexer->getTokenValue() === 'on') {
            throw $this->unexpected($lexer);
        }

        return $this->buildAST(ASTKindEnum::NAME, $lexer);
    }
}
