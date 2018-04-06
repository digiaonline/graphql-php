<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\Node\NodeInterface;

class Parser implements ParserInterface
{

    use LexerUtilsTrait;

    /**
     * @var ASTBuilderInterface
     */
    protected $astBuilder;

    /**
     * @var NodeBuilderInterface
     */
    protected $nodeBuilder;

    /**
     * Parser constructor.
     *
     * @param ASTBuilderInterface $astBuilder
     * @param NodeBuilderInterface $nodeBuilder
     */
    public function __construct(
        ASTBuilderInterface $astBuilder,
        NodeBuilderInterface $nodeBuilder
    ) {
        $this->astBuilder = $astBuilder;
        $this->nodeBuilder = $nodeBuilder;
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     */
    public function parse(LexerInterface $lexer): NodeInterface
    {
        return $this->nodeBuilder->build($this->parseAST($lexer));
    }

    /**
     * @param LexerInterface $lexer
     *
     * @return array
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function parseAST(LexerInterface $lexer): array
    {
        return $this->astBuilder->buildDocument($lexer);
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     */
    public function parseValue(LexerInterface $lexer): NodeInterface
    {
        return $this->nodeBuilder->build($this->parseValueAST($lexer));
    }

    /**
     * @param LexerInterface $lexer
     *
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseValueAST(LexerInterface $lexer): array
    {
        $this->expect($lexer, TokenKindEnum::SOF);
        $value = $this->astBuilder->buildValueLiteral($lexer, false);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $value;
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     */
    public function parseType(LexerInterface $lexer): NodeInterface
    {
        return $this->nodeBuilder->build($this->parseTypeAST($lexer));
    }

    /**
     * @param LexerInterface $lexer
     *
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseTypeAST(LexerInterface $lexer): array
    {
        $this->expect($lexer, TokenKindEnum::SOF);
        $type = $this->astBuilder->buildTypeReference($lexer);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $type;
    }
}
