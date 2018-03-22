<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\DirectiveLocationEnum;
use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class DirectiveDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::DIRECTIVE_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildAST(ASTKindEnum::DESCRIPTION, $lexer);

        $this->expectKeyword($lexer, KeywordEnum::DIRECTIVE);
        $this->expect($lexer, TokenKindEnum::AT);

        $name      = $this->buildAST(ASTKindEnum::NAME, $lexer);
        $arguments = $this->buildAST(ASTKindEnum::ARGUMENTS_DEFINITION, $lexer);

        $this->expectKeyword($lexer, KeywordEnum::ON);

        $locations = $this->parseDirectiveLocations($lexer);

        return [
            'kind'        => NodeKindEnum::DIRECTIVE_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'arguments'   => $arguments,
            'locations'   => $locations,
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function parseDirectiveLocations(LexerInterface $lexer): array
    {
        $this->skip($lexer, TokenKindEnum::PIPE);

        $locations = [];

        do {
            $locations[] = $this->parseDirectiveLocation($lexer);
        } while ($this->skip($lexer, TokenKindEnum::PIPE));

        return $locations;
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function parseDirectiveLocation(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $name = $this->buildAST(ASTKindEnum::NAME, $lexer);

        if (\in_array($name['value'], DirectiveLocationEnum::values(), true)) {
            return $name;
        }

        throw $this->unexpected($lexer, $start);
    }
}
