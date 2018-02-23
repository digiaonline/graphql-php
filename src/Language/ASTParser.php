<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Error\SyntaxError;
use Digia\GraphQL\Language\AST\Builder\Contract\BuilderInterface;
use Digia\GraphQL\Language\AST\Builder\Contract\DirectorInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\Contract\ParserInterface;
use Digia\GraphQL\Language\Reader\Contract\ReaderInterface;

class ASTParser implements ParserInterface, DirectorInterface
{

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
     * @param BuilderInterface[] $builders
     * @param ReaderInterface[]  $readers
     */
    public function __construct(array $builders, array $readers)
    {
        foreach ($builders as $builder) {
            $builder->setDirector($this);
        }

        $this->builders = $builders;
        $this->readers  = $readers;
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function parse(Source $source, array $options = []): NodeInterface
    {
        return $this->build($this->parseAST($source, $options));
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function parseValue(Source $source, array $options = []): NodeInterface
    {
        return $this->build($this->parseValueAST($source, $options));
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function parseType(Source $source, array $options = []): NodeInterface
    {
        return $this->build($this->parseTypeAST($source, $options));
    }

    /**
     * @param Source $source
     * @param array  $options
     * @return array
     * @throws GraphQLError
     */
    protected function parseAST(Source $source, array $options = []): array
    {
        $lexer = $this->createLexer($source, $options);

        return $this->parseDocument($lexer);
    }

    /**
     * @param Source $source
     * @param array  $options
     * @return array
     * @throws GraphQLError
     */
    protected function parseValueAST(Source $source, array $options = []): array
    {
        $lexer = $this->createLexer($source, $options);

        $this->expect($lexer, TokenKindEnum::SOF);
        $value = $this->parseValueLiteral($lexer, false);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $value;
    }

    /**
     * @param Source $source
     * @param array  $options
     * @return array
     * @throws GraphQLError
     */
    protected function parseTypeAST(Source $source, array $options = []): array
    {
        $lexer = $this->createLexer($source, $options);

        $this->expect($lexer, TokenKindEnum::SOF);
        $type = $this->parseTypeReference($lexer);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $type;
    }

    /**
     * @param array $ast
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function build(array $ast): NodeInterface
    {
        if (!isset($ast['kind'])) {
            throw new GraphQLError(sprintf('Nodes must specify a kind, got %s', json_encode($ast)));
        }

        $builder = $this->getBuilder($ast['kind']);

        if ($builder !== null) {
            return $builder->build($ast);
        }

        throw new GraphQLError(sprintf('Node of kind "%s" not supported.', $ast['kind']));
    }

    /**
     * @param string $kind
     * @return BuilderInterface|null
     */
    protected function getBuilder(string $kind): ?BuilderInterface
    {
        foreach ($this->builders as $builder) {
            if ($builder instanceof BuilderInterface && $builder->supportsKind($kind)) {
                return $builder;
            }
        }

        return null;
    }

    /**
     * @param Source $source
     * @param array  $options
     * @return Lexer
     */
    protected function createLexer(Source $source, array $options): Lexer
    {
        return new Lexer($source, $this->readers, $options);
    }

    /**
     * Determines if the next token is of a given kind.
     *
     * @param Lexer  $lexer
     * @param string $kind
     * @return bool
     */
    protected function peek(Lexer $lexer, string $kind): bool
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
    protected function skip(Lexer $lexer, string $kind): bool
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
    protected function expect(Lexer $lexer, string $kind): Token
    {
        $token = $lexer->getToken();

        if ($token->getKind() === $kind) {
            $lexer->advance();
            return $token;
        }

        throw new SyntaxError(sprintf('Expected %s, found %s', $kind, $token));
    }

    /**
     * @param Lexer  $lexer
     * @param string $value
     * @return Token
     * @throws GraphQLError
     */
    protected function expectKeyword(Lexer $lexer, string $value): Token
    {
        $token = $lexer->getToken();

        if ($token->getKind() === TokenKindEnum::NAME && $token->getValue() === $value) {
            $lexer->advance();
            return $token;
        }

        throw new SyntaxError(sprintf('Expected %s, found ${getTokenDesc(token)}', $value, $token));
    }

    /**
     * Helper function for creating an error when an unexpected lexed token
     * is encountered.
     *
     * @param Lexer      $lexer
     * @param Token|null $atToken
     * @return GraphQLError
     */
    protected function unexpected(Lexer $lexer, ?Token $atToken = null): GraphQLError
    {
        $token = $atToken ?: $lexer->getToken();

        return new SyntaxError(sprintf('Unexpected %s', $token));
    }

    /**
     * @param Lexer $lexer
     * @param Token $startToken
     * @return array
     */
    protected function createLocation(Lexer $lexer, Token $startToken): array
    {
        return [
            'startToken' => $startToken,
            'endToken'   => $lexer->getLastToken(),
        ];
    }

    /**
     * Returns a possibly empty list of parse nodes, determined by
     * the parseFn. This list begins with a lex token of openKind
     * and ends with a lex token of closeKind. Advances the parser
     * to the next lex token after the closing token.
     *
     * @param Lexer    $lexer
     * @param string   $openKind
     * @param callable $parseFunction
     * @param string   $closeKind
     * @return array
     * @throws GraphQLError
     */
    protected function any(Lexer $lexer, string $openKind, callable $parseFunction, string $closeKind): array
    {
        $this->expect($lexer, $openKind);

        $nodes = [];

        while (!$this->skip($lexer, $closeKind)) {
            $nodes[] = $parseFunction($lexer);
        }

        return $nodes;
    }

    /**
     * Returns a non-empty list of parse nodes, determined by
     * the parseFn. This list begins with a lex token of openKind
     * and ends with a lex token of closeKind. Advances the parser
     * to the next lex token after the closing token.
     *
     * @param Lexer    $lexer
     * @param string   $openKind
     * @param callable $parseFunction
     * @param string   $closeKind
     * @return array
     * @throws GraphQLError
     */
    protected function many(Lexer $lexer, string $openKind, callable $parseFunction, string $closeKind): array
    {
        $this->expect($lexer, $openKind);

        $nodes = [$parseFunction($lexer)];

        while (!$this->skip($lexer, $closeKind)) {
            $nodes[] = $parseFunction($lexer);
        }

        return $nodes;
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseName(Lexer $lexer): array
    {
        $token = $this->expect($lexer, TokenKindEnum::NAME);

        return [
            'kind'  => NodeKindEnum::NAME,
            'value' => $token->getValue(),
            'loc'   => $this->createLocation($lexer, $token),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseDocument(Lexer $lexer): array
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
            'loc'         => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseDefinition(Lexer $lexer): array
    {
        // TODO: Consider adding experimental support for parsing schema definitions

        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            switch ($lexer->getToken()->getValue()) {
                case 'query':
                case 'mutation':
                case 'subscription':
                case 'fragment':
                    return $this->parseExecutableDefinition($lexer);
                case 'schema':
                case 'scalar':
                case 'type':
                case 'interface':
                case 'union':
                case 'enum':
                case 'input':
                case 'extend':
                case 'directive':
                    // Note: The schema definition language is an experimental addition.
//                    return $this->parseTypeSystemDefinition($lexer);
            }
        } elseif ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            return $this->parseExecutableDefinition($lexer);
//        } elseif ($this->peekDescription($lexer)) {
//            // Note: The schema definition language is an experimental addition.
//            return $this->parseTypeSystemDefinition($lexer);
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseExecutableDefinition(Lexer $lexer): array
    {
        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            switch ($lexer->getToken()->getValue()) {
                case 'query':
                case 'mutation':
                case 'subscription':
                    return $this->parseOperationDefinition($lexer);
                case 'fragment':
                    return $this->parseFragmentDefinition($lexer);
            }
        } elseif ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            return $this->parseOperationDefinition($lexer);
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseOperationDefinition(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        if ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            return [
                'kind'                => NodeKindEnum::OPERATION_DEFINITION,
                'operation'           => 'query',
                'name'                => null,
                'variableDefinitions' => [],
                'directives'          => [],
                'selectionSet'        => $this->parseSelectionSet($lexer),
                'loc'                 => $this->createLocation($lexer, $start),
            ];
        }

        $operation = $this->parseOperationType($lexer);

        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            $name = $this->parseName($lexer);
        }

        return [
            'kind'                => NodeKindEnum::OPERATION_DEFINITION,
            'operation'           => $operation,
            'name'                => $name ?? null,
            'variableDefinitions' => $this->parseVariableDefinitions($lexer),
            'directives'          => $this->parseDirectives($lexer, false),
            'selectionSet'        => $this->parseSelectionSet($lexer),
            'loc'                 => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseVariableDefinitions(Lexer $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many($lexer, TokenKindEnum::PAREN_L, [$this, 'parseVariableDefinition'], TokenKindEnum::PAREN_R)
            : [];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseVariableDefinition(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param Lexer $lexer
         * @return mixed
         * @throws GraphQLError
         */
        $parseType = function (Lexer $lexer) {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->parseTypeReference($lexer);
        };

        return [
            'kind'         => NodeKindEnum::VARIABLE_DEFINITION,
            'variable'     => $this->parseVariable($lexer),
            'type'         => $parseType($lexer),
            'defaultValue' => $this->skip($lexer, TokenKindEnum::EQUALS)
                ? $this->parseValueLiteral($lexer, true)
                : null,
            'loc'          => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseVariable(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::DOLLAR);

        return [
            'kind' => NodeKindEnum::VARIABLE,
            'name' => $this->parseName($lexer),
            'loc'  => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseSelectionSet(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        return [
            'kind'       => NodeKindEnum::SELECTION_SET,
            'selections' => $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'parseSelection'],
                TokenKindEnum::BRACE_R
            ),
            'loc'        => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseSelection(Lexer $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::SPREAD)
            ? $this->parseFragment($lexer)
            : $this->parseField($lexer);
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseField(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        $nameOrAlias = $this->parseName($lexer);

        if ($this->skip($lexer, TokenKindEnum::COLON)) {
            $alias = $nameOrAlias;
            $name  = $this->parseName($lexer);
        } else {
            $name = $nameOrAlias;
        }

        return [
            'kind'         => NodeKindEnum::FIELD,
            'alias'        => $alias ?? null,
            'name'         => $name,
            'arguments'    => $this->parseArguments($lexer, false),
            'directives'   => $this->parseDirectives($lexer, false),
            'selectionSet' => $this->peek($lexer, TokenKindEnum::BRACE_L)
                ? $this->parseSelectionSet($lexer)
                : null,
            'loc'          => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseArguments(Lexer $lexer, bool $isConst): array
    {
        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::PAREN_L,
                [$this, $isConst ? 'parseConstArgument' : 'parseArgument'],
                TokenKindEnum::PAREN_R
            )
            : [];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseArgument(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param Lexer $lexer
         * @return mixed
         * @throws GraphQLError
         */
        $parseValue = function (Lexer $lexer) {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->parseValueLiteral($lexer, false);
        };

        return [
            'kind'  => NodeKindEnum::ARGUMENT,
            'name'  => $this->parseName($lexer),
            'value' => $parseValue($lexer),
            'loc'   => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseConstArgument(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param Lexer $lexer
         * @return mixed
         * @throws GraphQLError
         */
        $parseValue = function (Lexer $lexer) {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->parseConstValue($lexer);
        };

        return [
            'kind'  => NodeKindEnum::ARGUMENT,
            'name'  => $this->parseName($lexer),
            'value' => $parseValue($lexer),
            'loc'   => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseFragment(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::SPREAD);

        $tokenValue = $lexer->getToken()->getValue();

        if ($this->peek($lexer, TokenKindEnum::NAME) && $tokenValue !== 'on') {
            return [
                'kind'       => NodeKindEnum::FRAGMENT_SPREAD,
                'name'       => $this->parseFragmentName($lexer),
                'directives' => $this->parseDirectives($lexer, false),
                'loc'        => $this->createLocation($lexer, $start),
            ];
        }

        if ($tokenValue === 'on') {
            $lexer->advance();

            $typeCondition = $this->parseNamedType($lexer);
        }

        return [
            'kind'          => NodeKindEnum::INLINE_FRAGMENT,
            'typeCondition' => $typeCondition ?? null,
            'directives'    => $this->parseDirectives($lexer, false),
            'selectionSet'  => $this->parseSelectionSet($lexer),
            'loc'           => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseFragmentDefinition(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, 'fragment');

        // TODO: Consider adding experimental support fragment variables

        $parseTypeCondition = function (Lexer $lexer) {
            $this->expectKeyword($lexer, 'on');
            return $this->parseNamedType($lexer);
        };

        return [
            'kind'          => NodeKindEnum::FRAGMENT_DEFINITION,
            'name'          => $this->parseFragmentName($lexer),
            'typeCondition' => $parseTypeCondition,
            'directives'    => $this->parseDirectives($lexer, false),
            'selectionSet'  => $this->parseSelectionSet($lexer),
            'loc'           => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseFragmentName(Lexer $lexer): array
    {
        if ($lexer->getToken()->getValue() === 'on') {
            $this->unexpected($lexer);
        }

        return $this->parseName($lexer);
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseValueLiteral(Lexer $lexer, bool $isConst): array
    {
        $token = $lexer->getToken();

        switch ($token->getKind()) {
            case TokenKindEnum::BRACKET_L:
                return $this->parseList($lexer, $isConst);
            case TokenKindEnum::BRACE_L:
                return $this->parseObject($lexer, $isConst);
            case TokenKindEnum::INT:
                $lexer->advance();

                return [
                    'kind'  => NodeKindEnum::INT,
                    'value' => $token->getValue(),
                    'loc'   => $this->createLocation($lexer, $token),
                ];
            case TokenKindEnum::FLOAT:
                $lexer->advance();

                return [
                    'kind'  => NodeKindEnum::FLOAT,
                    'value' => $token->getValue(),
                    'loc'   => $this->createLocation($lexer, $token),
                ];
            case TokenKindEnum::STRING:
            case TokenKindEnum::BLOCK_STRING:
                return $this->parseStringLiteral($lexer);
            case TokenKindEnum::NAME:
                $value = $token->getValue();

                if ($value === 'true' || $value === 'false') {
                    $lexer->advance();

                    return [
                        'kind'  => NodeKindEnum::BOOLEAN,
                        'value' => $value === 'true',
                        'loc'   => $this->createLocation($lexer, $token),
                    ];
                } elseif ($value === 'null') {
                    $lexer->advance();

                    return [
                        'kind' => NodeKindEnum::NULL,
                        'loc'  => $this->createLocation($lexer, $token),
                    ];
                }

                $lexer->advance();

                return [
                    'kind'  => NodeKindEnum::ENUM,
                    'value' => $token->getValue(),
                    'loc'   => $this->createLocation($lexer, $token),
                ];
            case TokenKindEnum::DOLLAR:
                if (!$isConst) {
                    return $this->parseVariable($lexer);
                }
                break;
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseStringLiteral(Lexer $lexer): array
    {
        $token = $lexer->getToken();

        $lexer->advance();

        return [
            'kind'  => NodeKindEnum::STRING,
            'value' => $token->getValue(),
            'block' => $token->getKind() === TokenKindEnum::BLOCK_STRING,
            'loc'   => $this->createLocation($lexer, $token),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseConstValue(Lexer $lexer): array
    {
        return $this->parseValueLiteral($lexer, true);
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseValueValue(Lexer $lexer): array
    {
        return $this->parseValueLiteral($lexer, false);
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseList(Lexer $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        return [
            'kind'   => NodeKindEnum::LIST,
            'values' => $this->any(
                $lexer,
                TokenKindEnum::BRACKET_L,
                [$this, $isConst ? 'parseConstValue' : 'parseValueValue'],
                TokenKindEnum::BRACKET_R
            ),
            'loc'    => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseObject(Lexer $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::BRACE_L);

        $fields = [];

        while (!$this->skip($lexer, TokenKindEnum::BRACE_R)) {
            $fields[] = $this->parseObjectField($lexer, $isConst);
        }

        return [
            'kind'   => NodeKindEnum::OBJECT,
            'fields' => $fields,
            'loc'    => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseObjectField(Lexer $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        $parseValue = function (Lexer $lexer, bool $isConst) {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->parseValueLiteral($lexer, $isConst);
        };

        return [
            'kind'  => NodeKindEnum::OBJECT_FIELD,
            'name'  => $this->parseName($lexer),
            'value' => $parseValue($lexer, $isConst),
            'loc'   => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseDirectives(Lexer $lexer, bool $isConst): array
    {
        $directives = [];

        while ($this->peek($lexer, TokenKindEnum::AT)) {
            $directives[] = $this->parseDirective($lexer, $isConst);
        }

        return $directives;
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseDirective(Lexer $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::AT);

        return [
            'kind'      => NodeKindEnum::DIRECTIVE,
            'name'      => $this->parseName($lexer),
            'arguments' => $this->parseArguments($lexer, $isConst),
            'loc'       => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseTypeReference(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        if ($this->skip($lexer, TokenKindEnum::BRACKET_L)) {
            $type = $this->parseTypeReference($lexer);

            $this->expect($lexer, TokenKindEnum::BRACKET_R);

            $type = [
                'kind' => NodeKindEnum::LIST_TYPE,
                'type' => $type,
                'loc'  => $this->createLocation($lexer, $start),
            ];
        } else {
            $type = $this->parseNamedType($lexer);
        }

        if ($this->skip($lexer, TokenKindEnum::BANG)) {
            return [
                'kind' => NodeKindEnum::NON_NULL_TYPE,
                'type' => $type,
                'loc'  => $this->createLocation($lexer, $start),
            ];
        }

        return $type;
    }

    /**
     * @param Lexer $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseNamedType(Lexer $lexer): array
    {
        $start = $lexer->getToken();

        return [
            'kind' => NodeKindEnum::NAMED_TYPE,
            'name' => $this->parseName($lexer),
            'loc'  => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param Lexer $lexer
     * @return string
     * @throws GraphQLError
     */
    protected function parseOperationType(Lexer $lexer): string
    {
        $token = $this->expect($lexer, TokenKindEnum::NAME);
        $value = $token->getValue();

        if (isOperation($value)) {
            return $value;
        }

        throw $this->unexpected($lexer, $token);
    }
}
