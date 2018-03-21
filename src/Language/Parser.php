<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\ASTBuilder\ASTDirectorInterface;
use Digia\GraphQL\Language\ASTBuilder\ASTKindEnum;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\NodeBuilder\NodeDirectorInterface;

class Parser implements ParserInterface
{
    use LexerUtilsTrait;

    /**
     * @var ASTDirectorInterface
     */
    protected $astDirector;

    /**
     * @var NodeDirectorInterface
     */
    protected $nodeDirector;

    /**
     * Parser constructor.
     * @param ASTDirectorInterface  $astDirector
     * @param NodeDirectorInterface $nodeDirector
     */
    public function __construct(ASTDirectorInterface $astDirector, NodeDirectorInterface $nodeDirector)
    {
        $this->astDirector  = $astDirector;
        $this->nodeDirector = $nodeDirector;
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     */
    public function parse(LexerInterface $lexer): NodeInterface
    {
        return $this->nodeDirector->build($this->parseAST($lexer));
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     */
    public function parseValue(LexerInterface $lexer): NodeInterface
    {
        return $this->nodeDirector->build($this->parseValueAST($lexer));
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     */
    public function parseType(LexerInterface $lexer): NodeInterface
    {
        return $this->nodeDirector->build($this->parseTypeAST($lexer));
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function parseAST(LexerInterface $lexer): array
    {
        return $this->astDirector->build(ASTKindEnum::DOCUMENT, $lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseValueAST(LexerInterface $lexer): array
    {
        $this->expect($lexer, TokenKindEnum::SOF);
        $value = $this->astDirector->build(ASTKindEnum::VALUE_LITERAL, $lexer, ['isConst' => false]);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $value;
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseTypeAST(LexerInterface $lexer): array
    {
        $this->expect($lexer, TokenKindEnum::SOF);
        $type = $this->astDirector->build(ASTKindEnum::TYPE_REFERENCE, $lexer);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $type;
    }
}
