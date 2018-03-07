<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Error\SyntaxError;
use Digia\GraphQL\Language\AST\Builder\DirectorInterface;
use Digia\GraphQL\Language\AST\Builder\NodeBuilderInterface;
use Digia\GraphQL\Language\AST\DirectiveLocationEnum;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class Parser implements ParserInterface, DirectorInterface
{

    /**
     * @var NodeBuilderInterface
     */
    protected $builder;

    /**
     * Parser constructor.
     *
     * @param NodeBuilderInterface $builder
     */
    public function __construct(NodeBuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     * @throws \ReflectionException
     * @throws GraphQLError
     */
    public function parse(LexerInterface $lexer): NodeInterface
    {
        return $this->builder->build($this->parseAST($lexer));
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function parseValue(LexerInterface $lexer): NodeInterface
    {
        return $this->builder->build($this->parseValueAST($lexer));
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function parseType(LexerInterface $lexer): NodeInterface
    {
        return $this->builder->build($this->parseTypeAST($lexer));
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     * @throws \ReflectionException
     */
    protected function parseAST(LexerInterface $lexer): array
    {
        return $this->parseDocument($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseValueAST(LexerInterface $lexer): array
    {
        $this->expect($lexer, TokenKindEnum::SOF);
        $value = $this->parseValueLiteral($lexer, false);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $value;
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseTypeAST(LexerInterface $lexer): array
    {
        $this->expect($lexer, TokenKindEnum::SOF);
        $type = $this->parseTypeReference($lexer);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $type;
    }

    /**
     * @param array $ast
     * @return NodeInterface
     */
    public function build(array $ast): NodeInterface
    {
        return $this->builder->build($ast);
    }

    /**
     * Determines if the next token is of a given kind.
     *
     * @param LexerInterface $lexer
     * @param string         $kind
     * @return bool
     */
    protected function peek(LexerInterface $lexer, string $kind): bool
    {
        return $lexer->getTokenKind() === $kind;
    }

    /**
     * If the next token is of the given kind, return true after advancing
     * the lexer. Otherwise, do not change the parser state and return false.
     *
     * @param LexerInterface $lexer
     * @param string         $kind
     * @return bool
     * @throws GraphQLError
     */
    protected function skip(LexerInterface $lexer, string $kind): bool
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
     * @param LexerInterface $lexer
     * @param string         $kind
     * @return Token
     * @throws GraphQLError
     */
    protected function expect(LexerInterface $lexer, string $kind): Token
    {
        $token = $lexer->getToken();

        if ($token->getKind() === $kind) {
            $lexer->advance();
            return $token;
        }

        throw new SyntaxError(sprintf('Expected %s, found %s', $kind, $token));
    }

    /**
     * @param LexerInterface $lexer
     * @param string         $value
     * @return Token
     * @throws GraphQLError
     */
    protected function expectKeyword(LexerInterface $lexer, string $value): Token
    {
        $token = $lexer->getToken();

        if ($token->getKind() === TokenKindEnum::NAME && $token->getValue() === $value) {
            $lexer->advance();
            return $token;
        }

        throw new SyntaxError(sprintf('Expected %s, found %s', $value, $token));
    }

    /**
     * Helper function for creating an error when an unexpected lexed token
     * is encountered.
     *
     * @param LexerInterface $lexer
     * @param Token|null     $atToken
     * @return GraphQLError
     */
    protected function unexpected(LexerInterface $lexer, ?Token $atToken = null): GraphQLError
    {
        $token = $atToken ?: $lexer->getToken();

        return new SyntaxError(sprintf('Unexpected %s', $token));
    }

    /**
     * @param LexerInterface $lexer
     * @param Token          $startToken
     * @return array|null
     */
    protected function createLocation(LexerInterface $lexer, Token $startToken): ?array
    {
        return !$lexer->getOption('noLocation', false) ? [
            'start' => $startToken->getStart(),
            'end'   => $lexer->getLastToken()->getEnd(),
        ] : null;
    }

    /**
     * Returns a possibly empty list of parse nodes, determined by
     * the parseFn. This list begins with a lex token of openKind
     * and ends with a lex token of closeKind. Advances the parser
     * to the next lex token after the closing token.
     *
     * @param LexerInterface $lexer
     * @param string         $openKind
     * @param callable       $parseFunction
     * @param string         $closeKind
     * @return array
     * @throws GraphQLError
     */
    protected function any(LexerInterface $lexer, string $openKind, callable $parseFunction, string $closeKind): array
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
     * @param LexerInterface $lexer
     * @param string         $openKind
     * @param callable       $parseFunction
     * @param string         $closeKind
     * @return array
     * @throws GraphQLError
     */
    protected function many(LexerInterface $lexer, string $openKind, callable $parseFunction, string $closeKind): array
    {
        $this->expect($lexer, $openKind);

        $nodes = [$parseFunction($lexer)];

        while (!$this->skip($lexer, $closeKind)) {
            $nodes[] = $parseFunction($lexer);
        }

        return $nodes;
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseName(LexerInterface $lexer): array
    {
        $token = $this->expect($lexer, TokenKindEnum::NAME);

        return [
            'kind'  => NodeKindEnum::NAME,
            'value' => $token->getValue(),
            'loc'   => $this->createLocation($lexer, $token),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     * @throws \ReflectionException
     */
    protected function parseDocument(LexerInterface $lexer): array
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     * @throws \ReflectionException
     */
    protected function parseDefinition(LexerInterface $lexer): array
    {
        // TODO: Consider adding experimental support for parsing schema definitions

        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            switch ($lexer->getTokenValue()) {
                case KeywordEnum::QUERY:
                case KeywordEnum::MUTATION:
                case KeywordEnum::SUBSCRIPTION:
                case KeywordEnum::FRAGMENT:
                    return $this->parseExecutableDefinition($lexer);
                case KeywordEnum::SCHEMA:
                case KeywordEnum::SCALAR:
                case KeywordEnum::TYPE:
                case KeywordEnum::INTERFACE:
                case KeywordEnum::UNION:
                case KeywordEnum::ENUM:
                case KeywordEnum::INPUT:
                case KeywordEnum::EXTEND:
                case KeywordEnum::DIRECTIVE:
                    // Note: The schema definition language is an experimental addition.
                    return $this->parseTypeSystemDefinition($lexer);
            }
        } elseif ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            return $this->parseExecutableDefinition($lexer);
        } elseif ($this->peekDescription($lexer)) {
            // Note: The schema definition language is an experimental addition.
            return $this->parseTypeSystemDefinition($lexer);
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseExecutableDefinition(LexerInterface $lexer): array
    {
        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            switch ($lexer->getToken()->getValue()) {
                case KeywordEnum::QUERY:
                case KeywordEnum::MUTATION:
                case KeywordEnum::SUBSCRIPTION:
                    return $this->parseOperationDefinition($lexer);
                case KeywordEnum::FRAGMENT:
                    return $this->parseFragmentDefinition($lexer);
            }
        } elseif ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            return $this->parseOperationDefinition($lexer);
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseOperationDefinition(LexerInterface $lexer): array
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
     * @param LexerInterface $lexer
     * @return string
     * @throws GraphQLError
     */
    protected function parseOperationType(LexerInterface $lexer): string
    {
        $token = $this->expect($lexer, TokenKindEnum::NAME);
        $value = $token->getValue();

        if (isOperation($value)) {
            return $value;
        }

        throw $this->unexpected($lexer, $token);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseVariableDefinitions(LexerInterface $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many($lexer, TokenKindEnum::PAREN_L, [$this, 'parseVariableDefinition'], TokenKindEnum::PAREN_R)
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseVariableDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return mixed
         * @throws GraphQLError
         */
        $parseType = function (LexerInterface $lexer) {
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseVariable(LexerInterface $lexer): array
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseSelectionSet(LexerInterface $lexer): array
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseSelection(LexerInterface $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::SPREAD)
            ? $this->parseFragment($lexer)
            : $this->parseField($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseField(LexerInterface $lexer): array
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
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseArguments(LexerInterface $lexer, bool $isConst): array
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseArgument(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return mixed
         * @throws GraphQLError
         */
        $parseValue = function (LexerInterface $lexer) {
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseConstArgument(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return mixed
         * @throws GraphQLError
         */
        $parseValue = function (LexerInterface $lexer) {
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseFragment(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::SPREAD);

        $tokenValue = $lexer->getTokenValue();

        if ($tokenValue !== 'on' && $this->peek($lexer, TokenKindEnum::NAME)) {
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseFragmentDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::FRAGMENT);

        $parseTypeCondition = function (LexerInterface $lexer) {
            $this->expectKeyword($lexer, 'on');
            return $this->parseNamedType($lexer);
        };

        return [
            'kind'                => NodeKindEnum::FRAGMENT_DEFINITION,
            'name'                => $this->parseFragmentName($lexer),
            'variableDefinitions' => $this->parseVariableDefinitions($lexer),
            'typeCondition'       => $parseTypeCondition($lexer),
            'directives'          => $this->parseDirectives($lexer, false),
            'selectionSet'        => $this->parseSelectionSet($lexer),
            'loc'                 => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseFragmentName(LexerInterface $lexer): array
    {
        if ($lexer->getTokenValue() === 'on') {
            throw $this->unexpected($lexer);
        }

        return $this->parseName($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseValueLiteral(LexerInterface $lexer, bool $isConst): array
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseStringLiteral(LexerInterface $lexer): array
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseConstValue(LexerInterface $lexer): array
    {
        return $this->parseValueLiteral($lexer, true);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseValueValue(LexerInterface $lexer): array
    {
        return $this->parseValueLiteral($lexer, false);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseList(LexerInterface $lexer, bool $isConst): array
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
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseObject(LexerInterface $lexer, bool $isConst): array
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
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseObjectField(LexerInterface $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        $parseValue = function (LexerInterface $lexer, bool $isConst) {
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
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseDirectives(LexerInterface $lexer, bool $isConst): array
    {
        $directives = [];

        while ($this->peek($lexer, TokenKindEnum::AT)) {
            $directives[] = $this->parseDirective($lexer, $isConst);
        }

        return $directives;
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws GraphQLError
     */
    protected function parseDirective(LexerInterface $lexer, bool $isConst): array
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
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
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseNamedType(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        return [
            'kind' => NodeKindEnum::NAMED_TYPE,
            'name' => $this->parseName($lexer),
            'loc'  => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     * @throws \ReflectionException
     */
    protected function parseTypeSystemDefinition(LexerInterface $lexer): array
    {
        // Many definitions begin with a description and require a lookahead.
        $keywordToken = $this->peekDescription($lexer) ? $lexer->lookahead() : $lexer->getToken();

        if ($keywordToken->getKind() === TokenKindEnum::NAME) {
            switch ($keywordToken->getValue()) {
                case KeywordEnum::SCHEMA:
                    return $this->parseSchemaDefinition($lexer);
                case KeywordEnum::SCALAR:
                    return $this->parseScalarTypeDefinition($lexer);
                case KeywordEnum::TYPE:
                    return $this->parseObjectTypeDefinition($lexer);
                case KeywordEnum::INTERFACE:
                    return $this->parseInterfaceTypeDefinition($lexer);
                case KeywordEnum::UNION:
                    return $this->parseUnionTypeDefinition($lexer);
                case KeywordEnum::ENUM:
                    return $this->parseEnumTypeDefinition($lexer);
                case KeywordEnum::INPUT:
                    return $this->parseInputObjectTypeDefinition($lexer);
                case KeywordEnum::EXTEND:
                    return $this->parseTypeExtension($lexer);
                case KeywordEnum::DIRECTIVE:
                    return $this->parseDirectiveDefinition($lexer);
            }
        }

        throw $this->unexpected($lexer, $keywordToken);
    }

    /**
     * @param LexerInterface $lexer
     * @return bool
     */
    protected function peekDescription(LexerInterface $lexer): bool
    {
        return $this->peek($lexer, TokenKindEnum::STRING) || $this->peek($lexer, TokenKindEnum::BLOCK_STRING);
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws GraphQLError
     */
    protected function parseDescription(LexerInterface $lexer): ?array
    {
        return $this->peekDescription($lexer) ? $this->parseStringLiteral($lexer) : null;
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseSchemaDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::SCHEMA);

        $directives = $this->parseDirectives($lexer, true);

        $operationTypes = $this->many(
            $lexer,
            TokenKindEnum::BRACE_L,
            [$this, 'parseOperationTypeDefinition'],
            TokenKindEnum::BRACE_R
        );

        return [
            'kind'           => NodeKindEnum::SCHEMA_DEFINITION,
            'directives'     => $directives,
            'operationTypes' => $operationTypes,
            'loc'            => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseOperationTypeDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $operation = $this->parseOperationType($lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        $type = $this->parseNamedType($lexer);

        return [
            'kind'      => NodeKindEnum::OPERATION_TYPE_DEFINITION,
            'operation' => $operation,
            'type'      => $type,
            'loc'       => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseScalarTypeDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::SCALAR);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);

        return [
            'kind'        => NodeKindEnum::SCALAR_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'directives'  => $directives,
            'loc'         => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseObjectTypeDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::TYPE);

        $name       = $this->parseName($lexer);
        $interfaces = $this->parseImplementsInterfaces($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $fields     = $this->parseFieldsDefinition($lexer);

        return [
            'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'interfaces'  => $interfaces,
            'directives'  => $directives,
            'fields'      => $fields,
            'loc'         => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseImplementsInterfaces(LexerInterface $lexer): array
    {
        $types = [];

        if ($lexer->getTokenValue() === 'implements') {
            $lexer->advance();

            // Optional leading ampersand
            $this->skip($lexer, TokenKindEnum::AMP);

            do {
                $types[] = $this->parseNamedType($lexer);
            } while ($this->skip($lexer, TokenKindEnum::AMP));
        }

        return $types;
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseFieldsDefinition(LexerInterface $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many($lexer, TokenKindEnum::BRACE_L, [$this, 'parseFieldDefinition'], TokenKindEnum::BRACE_R)
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseFieldDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);
        $name        = $this->parseName($lexer);
        $arguments   = $this->parseArgumentsDefinition($lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        $type       = $this->parseTypeReference($lexer);
        $directives = $this->parseDirectives($lexer, true);

        return [
            'kind'        => NodeKindEnum::FIELD_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'arguments'   => $arguments,
            'type'        => $type,
            'directives'  => $directives,
            'loc'         => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseArgumentsDefinition(LexerInterface $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many($lexer, TokenKindEnum::PAREN_L, [$this, 'parseInputValueDefinition'], TokenKindEnum::PAREN_R)
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseInputValueDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);
        $name        = $this->parseName($lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        $type         = $this->parseTypeReference($lexer);
        $defaultValue = $this->skip($lexer, TokenKindEnum::EQUALS) ? $this->parseConstValue($lexer) : null;
        $directives   = $this->parseDirectives($lexer, true);

        return [
            'kind'         => NodeKindEnum::INPUT_VALUE_DEFINITION,
            'description'  => $description,
            'name'         => $name,
            'type'         => $type,
            'defaultValue' => $defaultValue,
            'directives'   => $directives,
            'loc'          => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseInterfaceTypeDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::INTERFACE);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $fields     = $this->parseFieldsDefinition($lexer);

        return [
            'kind'        => NodeKindEnum::INTERFACE_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'directives'  => $directives,
            'fields'      => $fields,
            'loc'         => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseUnionTypeDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::UNION);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $types      = $this->parseUnionMemberTypes($lexer);

        return [
            'kind'        => NodeKindEnum::UNION_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'directives'  => $directives,
            'types'       => $types,
            'loc'         => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseUnionMemberTypes(LexerInterface $lexer): array
    {
        $types = [];

        if ($this->skip($lexer, TokenKindEnum::EQUALS)) {
            // Optional leading pipe
            $this->skip($lexer, TokenKindEnum::PIPE);

            do {
                $types[] = $this->parseNamedType($lexer);
            } while ($this->skip($lexer, TokenKindEnum::PIPE));
        }

        return $types;
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseEnumTypeDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::ENUM);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $values     = $this->parseEnumValuesDefinition($lexer);

        return [
            'kind'        => NodeKindEnum::ENUM_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'directives'  => $directives,
            'values'      => $values,
            'loc'         => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseEnumValuesDefinition(LexerInterface $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many($lexer, TokenKindEnum::BRACE_L, [$this, 'parseEnumValueDefinition'], TokenKindEnum::BRACE_R)
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseEnumValueDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);
        $name        = $this->parseName($lexer);
        $directives  = $this->parseDirectives($lexer, true);

        return [
            'kind'        => NodeKindEnum::ENUM_VALUE_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'directives'  => $directives,
            'loc'         => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseInputObjectTypeDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::INPUT);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $fields     = $this->parseInputFieldsDefinition($lexer);

        return [
            'kind'        => NodeKindEnum::INPUT_OBJECT_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'directives'  => $directives,
            'fields'      => $fields,
            'loc'         => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseInputFieldsDefinition(LexerInterface $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many($lexer, TokenKindEnum::BRACE_L, [$this, 'parseInputValueDefinition'], TokenKindEnum::BRACE_R)
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseTypeExtension(LexerInterface $lexer): array
    {
        $keywordToken = $lexer->lookahead();

        if ($keywordToken->getKind() === TokenKindEnum::NAME) {
            switch ($keywordToken->getValue()) {
                case KeywordEnum::SCALAR:
                    return $this->parseScalarTypeExtension($lexer);
                case KeywordEnum::TYPE:
                    return $this->parseObjectTypeExtension($lexer);
                case KeywordEnum::INTERFACE:
                    return $this->parseInterfaceTypeExtension($lexer);
                case KeywordEnum::UNION:
                    return $this->parseUnionTypeExtension($lexer);
                case KeywordEnum::ENUM:
                    return $this->parseEnumTypeExtension($lexer);
                case KeywordEnum::INPUT:
                    return $this->parseInputObjectTypeExtension($lexer);
            }
        }

        throw $this->unexpected($lexer, $keywordToken);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseScalarTypeExtension(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::SCALAR);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);

        if (count($directives) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::SCALAR_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'loc'        => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseObjectTypeExtension(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::TYPE);

        $name       = $this->parseName($lexer);
        $interfaces = $this->parseImplementsInterfaces($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $fields     = $this->parseFieldsDefinition($lexer);

        if (count($interfaces) === 0 && count($directives) === 0 && count($fields) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::OBJECT_TYPE_EXTENSION,
            'name'       => $name,
            'interfaces' => $interfaces,
            'directives' => $directives,
            'fields'     => $fields,
            'loc'        => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseInterfaceTypeExtension(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::INTERFACE);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $fields     = $this->parseFieldsDefinition($lexer);

        if (count($directives) === 0 && count($fields) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::INTERFACE_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'fields'     => $fields,
            'loc'        => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseUnionTypeExtension(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::UNION);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $types      = $this->parseUnionMemberTypes($lexer);

        if (count($directives) === 0 && count($types) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::UNION_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'types'      => $types,
            'loc'        => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseEnumTypeExtension(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::ENUM);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $values     = $this->parseEnumValuesDefinition($lexer);

        if (count($directives) === 0 && count($values) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::ENUM_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'values'     => $values,
            'loc'        => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     */
    protected function parseInputObjectTypeExtension(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::INPUT);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $fields     = $this->parseInputFieldsDefinition($lexer);

        if (count($directives) === 0 && count($fields) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::INPUT_OBJECT_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'fields'     => $fields,
            'loc'        => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
     * @throws \ReflectionException
     */
    protected function parseDirectiveDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::DIRECTIVE);
        $this->expect($lexer, TokenKindEnum::AT);

        $name      = $this->parseName($lexer);
        $arguments = $this->parseArgumentsDefinition($lexer);

        $this->expectKeyword($lexer, KeywordEnum::ON);

        $locations = $this->parseDirectiveLocations($lexer);

        return [
            'kind'        => NodeKindEnum::DIRECTIVE_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'arguments'   => $arguments,
            'locations'   => $locations,
            'loc'         => $this->createLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws GraphQLError
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
     * @throws GraphQLError
     * @throws \ReflectionException
     */
    protected function parseDirectiveLocation(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $name = $this->parseName($lexer);

        if (\in_array($name['value'], DirectiveLocationEnum::values(), true)) {
            return $name;
        }

        throw $this->unexpected($lexer, $start);
    }
}
