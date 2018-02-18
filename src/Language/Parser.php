<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\Builder\Contract\BuilderInterface;
use Digia\GraphQL\Language\Reader\Contract\ReaderInterface;

class Parser
{

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array|BuilderInterface[]
     */
    protected $builders = [];

    /**
     * @var array|ReaderInterface[]
     */
    protected $readers = [];

    /**
     * Parser constructor.
     *
     * @param Lexer $lexer
     * @param array $options
     */
    public function __construct(array $builders, array $readers, array $options = [])
    {
        foreach ($builders as $builder) {
            $builder->setParser($this);
        }

        $this->builders = $builders;
        $this->readers  = $readers;
        $this->options  = $options;
    }

    /**
     * Given a GraphQL source, parses it into a Document.
     * Throws GraphQLError if a syntax error is encountered.
     *
     * @param Source $source
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function parse(Source $source): NodeInterface
    {
        $lexer = new Lexer($source, $this->readers);

        return $this->build($lexer, null);
    }

    /**
     * @param Lexer                 $lexer
     * @param BuilderInterface|null $parent
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function build(Lexer $lexer, ?BuilderInterface $parent): NodeInterface
    {
        $builder = $this->getBuilder($lexer, $parent);

        if ($builder !== null) {
            return $builder->build($lexer);
        }

        throw new GraphQLError(sprintf('Cannot find builder for token: %s', $lexer->getToken()));
    }

    /**
     * Determines if the next token is of a given kind.
     *
     * @param Lexer  $lexer
     * @param string $kind
     * @return bool
     */
    public function peek(Lexer $lexer, string $kind): bool
    {
        return $lexer->getToken()->getKind() === $kind;
    }

    /**
     * If the next token is of the given kind, return true after advancing
     * the lexer. Otherwise, do not change the parser state and return false.
     *
     * @param Lexer  $lexer
     * @param string $kind
     * @return bool
     * @throws GraphQLError
     */
    public function skip(Lexer $lexer, string $kind): bool
    {
        if ($match = $this->peek($lexer, $kind)) {
            $lexer->advance();
        }

        return $match;
    }

    /**
     * If the next token is of the given kind, return that token after advancing
     * the lexer. Otherwise, do not change the parser state and throw an error.
     *
     * @param Lexer  $lexer
     * @param string $kind
     * @return Token
     * @throws GraphQLError
     */
    public function expect(Lexer $lexer, string $kind): Token
    {
        $token = $lexer->getToken();

        if ($token->getKind() === $kind) {
            $lexer->advance();
            return $token;
        }

        throw new GraphQLError(sprintf('Expected %s, found %s', $kind, $token));
    }

    /**
     * Helper function for creating an error when an unexpected lexed token
     * is encountered.
     *
     * @param Lexer      $lexer
     * @param Token|null $atToken
     * @return GraphQLError
     */
    public function unexpected(Lexer $lexer, ?Token $atToken): GraphQLError
    {
        $token = $atToken ?: $lexer->getToken();

        return new GraphQLError(sprintf('Unexpected %s', $token));
    }

    /**
     * @param Lexer $lexer
     * @param Token $startToken
     * @return Location
     */
    public function createLocation(Lexer $lexer, Token $startToken): Location
    {
        return new Location($startToken, $lexer->getLastToken(), $lexer->getSource());
    }

    /**
     * Returns a possibly empty list of parse nodes, determined by
     * the parseFn. This list begins with a lex token of openKind
     * and ends with a lex token of closeKind. Advances the parser
     * to the next lex token after the closing token.
     *
     * @param Lexer  $lexer
     * @param string $openKind
     * @param string $closeKind
     * @return array|NodeInterface[]
     * @throws GraphQLError
     */
    public function any(Lexer $lexer, string $openKind, string $closeKind, ?BuilderInterface $parent): array
    {
        $this->expect($lexer, $openKind);

        $nodes = [];

        while (!$this->skip($lexer, $closeKind)) {
            $nodes[] = $this->build($lexer, $parent);
        }

        return $nodes;
    }

    /**
     * Returns a non-empty list of parse nodes, determined by
     * the parseFn. This list begins with a lex token of openKind
     * and ends with a lex token of closeKind. Advances the parser
     * to the next lex token after the closing token.
     *
     * @param Lexer  $lexer
     * @param string $openKind
     * @param string $closeKind
     * @return array|NodeInterface[]
     * @throws GraphQLError
     */
    public function many(Lexer $lexer, string $openKind, string $closeKind, ?BuilderInterface $parent): array
    {
        $this->expect($lexer, $openKind);

        $nodes = [$this->build($lexer, $parent)];

        while (!$this->skip($lexer, $closeKind)) {
            $nodes[] = $this->build($lexer, $parent);
        };

        return $nodes;
    }

    /**
     * @param Lexer                 $lexer
     * @param BuilderInterface|null $parent
     * @return BuilderInterface|null
     */
    protected function getBuilder(Lexer $lexer, ?BuilderInterface $parent): ?BuilderInterface
    {
        foreach ($this->builders as $builder) {
            if ($builder instanceof BuilderInterface && $builder->supportsBuilder($lexer, $parent)) {
                return $builder;
            }
        }

        return null;
    }
}
