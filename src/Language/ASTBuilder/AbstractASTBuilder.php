<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\LexerUtilsTrait;

abstract class AbstractASTBuilder implements ASTBuilderInterface
{
    use LexerUtilsTrait;

    /**
     * @var ASTDirectorInterface
     */
    protected $director;

    /**
     * @param ASTDirectorInterface $director
     * @return $this
     */
    public function setDirector(ASTDirectorInterface $director)
    {
        $this->director = $director;
        return $this;
    }

    /**
     * Builds the AST using the given Lexer.
     *
     * @param string         $kind
     * @param LexerInterface $lexer
     * @param array          $params
     * @return array|null
     */
    protected function buildAST(string $kind, LexerInterface $lexer, array $params = []): ?array
    {
        return $this->director->build($kind, $lexer, $params);
    }
}
