<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Error\SyntaxError;
use Digia\GraphQL\Language\AST\Node\ArgumentNode;
use Digia\GraphQL\Language\AST\Node\BooleanValueNode;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\ExecutableDefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\SelectionNodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\TypeNodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\ValueNodeInterface;
use Digia\GraphQL\Language\AST\Node\DirectiveNode;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\EnumValueNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\AST\Node\InlineFragmentNode;
use Digia\GraphQL\Language\AST\Node\IntValueNode;
use Digia\GraphQL\Language\AST\Node\ListTypeNode;
use Digia\GraphQL\Language\AST\Node\ListValueNode;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NameNode;
use Digia\GraphQL\Language\AST\Node\NonNullTypeNode;
use Digia\GraphQL\Language\AST\Node\NullValueNode;
use Digia\GraphQL\Language\AST\Node\ObjectFieldNode;
use Digia\GraphQL\Language\AST\Node\ObjectValueNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use Digia\GraphQL\Language\AST\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\AST\Node\VariableNode;
use Digia\GraphQL\Language\Contract\ParserInterface;
use Digia\GraphQL\Language\Reader\Contract\ReaderInterface;

class NodeParser implements ParserInterface
{

    /**
     * @var array|ReaderInterface[]
     */
    protected $readers = [];

    /**
     * Parser constructor.
     *
     * @param array $readers
     * @param array $options
     */
    public function __construct(array $readers)
    {
        $this->readers = $readers;
    }

    /**
     * @inheritdoc
     * @return DocumentNode
     * @throws GraphQLError
     */
    public function parse(Source $source, array $options = []): DocumentNode
    {
        $lexer = $this->createLexer($source, $options);

        return $this->parseDocument($lexer);
    }

    /**
     * @inheritdoc
     * @return ValueNodeInterface
     * @throws GraphQLError
     */
    public function parseValue(Source $source, array $options = []): ValueNodeInterface
    {
        $lexer = $this->createLexer($source, $options);

        $this->expect($lexer, TokenKindEnum::SOF);
        $value = $this->parseValueLiteral($lexer, false);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $value;
    }

    /**
     * @inheritdoc
     * @return TypeNodeInterface
     * @throws GraphQLError
     */
    public function parseType(Source $source, array $options = []): TypeNodeInterface
    {
        $lexer = $this->createLexer($source, $options);

        $this->expect($lexer, TokenKindEnum::SOF);
        $type = $this->parseTypeReference($lexer);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $type;
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

        throw new GraphQLError(sprintf('Expected %s, found %s', $kind, $token));
    }

    /**
     * @param Lexer  $lexer
     * @param string $value
     * @return Token
     * @throws GraphQLError
     * @throws SyntaxError
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

        return new GraphQLError(sprintf('Unexpected %s', $token));
    }

    /**
     * @param Lexer $lexer
     * @param Token $startToken
     * @return Location
     */
    protected function createLocation(Lexer $lexer, Token $startToken): Location
    {
        return new Location($startToken, $lexer->getLastToken(), $lexer->getSource());
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
     * @return array|NodeInterface[]
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
     * @return array|NodeInterface[]
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
     * @return NameNode
     * @throws GraphQLError
     */
    protected function parseName(Lexer $lexer): NameNode
    {
        $token = $this->expect($lexer, TokenKindEnum::NAME);

        return new NameNode([
            'value' => $token->getValue(),
            'loc'   => $this->createLocation($lexer, $token),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return DocumentNode
     * @throws GraphQLError
     */
    protected function parseDocument(Lexer $lexer): DocumentNode
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::SOF);

        $definitions = [];

        do {
            $definitions[] = $this->parseDefinition($lexer);
        } while (!$this->skip($lexer, TokenKindEnum::EOF));

        return new DocumentNode([
            'definitions' => $definitions,
            'loc'         => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return DefinitionNodeInterface
     * @throws GraphQLError
     */
    protected function parseDefinition(Lexer $lexer): DefinitionNodeInterface
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
     * @return ExecutableDefinitionNodeInterface
     * @throws GraphQLError
     */
    protected function parseExecutableDefinition(Lexer $lexer): ExecutableDefinitionNodeInterface
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
     * @return OperationDefinitionNode
     * @throws GraphQLError
     */
    protected function parseOperationDefinition(Lexer $lexer): OperationDefinitionNode
    {
        $start = $lexer->getToken();

        if ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            return new OperationDefinitionNode([
                'operation'           => 'query',
                'name'                => null,
                'variableDefinitions' => [],
                'directives'          => [],
                'selectionSet'        => $this->parseSelectionSet($lexer),
                'loc'                 => $this->createLocation($lexer, $start),
            ]);
        }

        $operation = $this->parseOperationType($lexer);

        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            $name = $this->parseName($lexer);
        }

        return new OperationDefinitionNode([
            'operation'           => $operation,
            'name'                => $name ?? null,
            'variableDefinitions' => $this->parseVariableDefinitions($lexer),
            'directives'          => $this->parseDirectives($lexer, false),
            'selectionSet'        => $this->parseSelectionSet($lexer),
            'loc'                 => $this->createLocation($lexer, $start),
        ]);
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
     * @return VariableDefinitionNode
     * @throws GraphQLError
     */
    protected function parseVariableDefinition(Lexer $lexer): VariableDefinitionNode
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

        return new VariableDefinitionNode([
            'variable'     => $this->parseVariable($lexer),
            'type'         => $parseType($lexer),
            'defaultValue' => $this->skip($lexer, TokenKindEnum::EQUALS)
                ? $this->parseValueLiteral($lexer, true)
                : null,
            'loc'          => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return VariableNode
     * @throws GraphQLError
     */
    protected function parseVariable(Lexer $lexer): VariableNode
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::DOLLAR);

        return new VariableNode([
            'name' => $this->parseName($lexer),
            'loc'  => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return SelectionSetNode
     * @throws GraphQLError
     */
    protected function parseSelectionSet(Lexer $lexer): SelectionSetNode
    {
        $start = $lexer->getToken();

        return new SelectionSetNode([
            'selections' => $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'parseSelection'],
                TokenKindEnum::BRACE_R
            ),
            'loc'        => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return SelectionNodeInterface
     * @throws GraphQLError
     */
    protected function parseSelection(Lexer $lexer): SelectionNodeInterface
    {
        return $this->peek($lexer, TokenKindEnum::SPREAD)
            ? $this->parseFragment($lexer)
            : $this->parseField($lexer);
    }

    /**
     * @param Lexer $lexer
     * @return FieldNode
     * @throws GraphQLError
     */
    protected function parseField(Lexer $lexer): FieldNode
    {
        $start = $lexer->getToken();

        $nameOrAlias = $this->parseName($lexer);

        if ($this->skip($lexer, TokenKindEnum::COLON)) {
            $alias = $nameOrAlias;
            $name  = $this->parseName($lexer);
        } else {
            $name = $nameOrAlias;
        }

        return new FieldNode([
            'alias'        => $alias ?? null,
            'name'         => $name,
            'arguments'    => $this->parseArguments($lexer, false),
            'directives'   => $this->parseDirectives($lexer, false),
            'selectionSet' => $this->peek($lexer, TokenKindEnum::BRACE_L)
                ? $this->parseSelectionSet($lexer)
                : null,
            'loc'          => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return array|ArgumentNode[]
     * @throws GraphQLError
     */
    protected function parseArguments(Lexer $lexer, bool $isConst): array
    {
        $parseFunction = $isConst ? 'parseConstArgument' : 'parseArgument';

        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many($lexer, TokenKindEnum::PAREN_L, [$this, $parseFunction], TokenKindEnum::PAREN_R)
            : [];
    }

    /**
     * @param Lexer $lexer
     * @return ArgumentNode
     * @throws GraphQLError
     */
    protected function parseArgument(Lexer $lexer): ArgumentNode
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

        return new ArgumentNode([
            'name'  => $this->parseName($lexer),
            'value' => $parseValue($lexer),
            'loc'   => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return ArgumentNode
     * @throws GraphQLError
     */
    protected function parseConstArgument(Lexer $lexer): ArgumentNode
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

        return new ArgumentNode([
            'name'  => $this->parseName($lexer),
            'value' => $parseValue($lexer),
            'loc'   => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return FragmentSpreadNode|InlineFragmentNode|SelectionNodeInterface
     * @throws GraphQLError
     */
    protected function parseFragment(Lexer $lexer): SelectionNodeInterface
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::SPREAD);

        $tokenValue = $lexer->getToken()->getValue();

        if ($this->peek($lexer, TokenKindEnum::NAME) && $tokenValue !== 'on') {
            return new FragmentSpreadNode([
                'name'       => $this->parseFragmentName($lexer),
                'directives' => $this->parseDirectives($lexer, false),
                'loc'        => $this->createLocation($lexer, $start),
            ]);
        }

        if ($tokenValue === 'on') {
            $lexer->advance();

            $typeCondition = $this->parseNamedType($lexer);
        }

        return new InlineFragmentNode([
            'typeCondition' => $typeCondition ?? null,
            'directives'    => $this->parseDirectives($lexer, false),
            'selectionSet'  => $this->parseSelectionSet($lexer),
            'loc'           => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return FragmentDefinitionNode
     * @throws GraphQLError
     */
    protected function parseFragmentDefinition(Lexer $lexer): FragmentDefinitionNode
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, 'fragment');

        // TODO: Consider adding experimental support fragment variables

        $parseTypeCondition = function (Lexer $lexer) {
            $this->expectKeyword($lexer, 'on');
            return $this->parseNamedType($lexer);
        };

        return new FragmentDefinitionNode([
            'name'          => $this->parseFragmentName($lexer),
            'typeCondition' => $parseTypeCondition,
            'directives'    => $this->parseDirectives($lexer, false),
            'selectionSet'  => $this->parseSelectionSet($lexer),
            'loc'           => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return NameNode
     * @throws GraphQLError
     */
    protected function parseFragmentName(Lexer $lexer): NameNode
    {
        if ($lexer->getToken()->getValue() === 'on') {
            $this->unexpected($lexer);
        }

        return $this->parseName($lexer);
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return ValueNodeInterface
     * @throws GraphQLError
     */
    protected function parseValueLiteral(Lexer $lexer, bool $isConst): ValueNodeInterface
    {
        $token = $lexer->getToken();

        switch ($token->getKind()) {
            case TokenKindEnum::BRACKET_L:
                return $this->parseList($lexer, $isConst);
            case TokenKindEnum::BRACE_L:
                return $this->parseObject($lexer, $isConst);
            case TokenKindEnum::INT:
                $lexer->advance();

                return new IntValueNode([
                    'value' => $token->getValue(),
                    'loc'   => $this->createLocation($lexer, $token),
                ]);
            case TokenKindEnum::FLOAT:
                $lexer->advance();

                return new IntValueNode([
                    'value' => $token->getValue(),
                    'loc'   => $this->createLocation($lexer, $token),
                ]);
            case TokenKindEnum::STRING:
            case TokenKindEnum::BLOCK_STRING:
                return $this->parseStringLiteral($lexer);
            case TokenKindEnum::NAME:
                $value = $token->getValue();

                if ($value === 'true' || $value === 'false') {
                    $lexer->advance();

                    return new BooleanValueNode([
                        'value' => $value === 'true',
                        'loc'   => $this->createLocation($lexer, $token),
                    ]);
                } elseif ($value === 'null') {
                    $lexer->advance();

                    return new NullValueNode([
                        'loc' => $this->createLocation($lexer, $token),
                    ]);
                }

                $lexer->advance();

                return new EnumValueNode([
                    'value' => $token->getValue(),
                    'loc'   => $this->createLocation($lexer, $token),
                ]);
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
     * @return StringValueNode
     * @throws GraphQLError
     */
    protected function parseStringLiteral(Lexer $lexer): StringValueNode
    {
        $token = $lexer->getToken();

        $lexer->advance();

        return new StringValueNode([
            'value' => $token->getValue(),
            'block' => $token->getKind() === TokenKindEnum::BLOCK_STRING,
            'loc'   => $this->createLocation($lexer, $token),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return ValueNodeInterface
     * @throws GraphQLError
     */
    protected function parseConstValue(Lexer $lexer): ValueNodeInterface
    {
        return $this->parseValueLiteral($lexer, true);
    }

    /**
     * @param Lexer $lexer
     * @return ValueNodeInterface
     * @throws GraphQLError
     */
    protected function parseValueValue(Lexer $lexer): ValueNodeInterface
    {
        return $this->parseValueLiteral($lexer, false);
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return ListValueNode
     * @throws GraphQLError
     */
    protected function parseList(Lexer $lexer, bool $isConst): ListValueNode
    {
        $start = $lexer->getToken();

        $parseFunction = $isConst ? 'parseConstValue' : 'parseValueValue';

        return new ListValueNode([
            'values' => $this->any($lexer, TokenKindEnum::BRACKET_L, [$this, $parseFunction], TokenKindEnum::BRACKET_R),
            'loc'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return ObjectValueNode
     * @throws GraphQLError
     */
    protected function parseObject(Lexer $lexer, bool $isConst): ObjectValueNode
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::BRACE_L);

        $fields = [];

        while (!$this->skip($lexer, TokenKindEnum::BRACE_R)) {
            $fields[] = $this->parseObjectField($lexer, $isConst);
        }

        return new ObjectValueNode([
            'fields' => $fields,
            'loc'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return ObjectFieldNode
     * @throws GraphQLError
     */
    protected function parseObjectField(Lexer $lexer, bool $isConst): ObjectFieldNode
    {
        $start = $lexer->getToken();

        $parseValue = function (Lexer $lexer, bool $isConst) {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->parseValueLiteral($lexer, $isConst);
        };

        return new ObjectFieldNode([
            'name'  => $this->parseName($lexer),
            'value' => $parseValue($lexer, $isConst),
            'loc'   => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @param bool  $isConst
     * @return array|DirectiveNode[]
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
     * @return DirectiveNode
     * @throws GraphQLError
     */
    protected function parseDirective(Lexer $lexer, bool $isConst): DirectiveNode
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::AT);

        return new DirectiveNode([
            'name'      => $this->parseName($lexer),
            'arguments' => $this->parseArguments($lexer, $isConst),
            'loc'       => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param Lexer $lexer
     * @return TypeNodeInterface
     * @throws GraphQLError
     */
    protected function parseTypeReference(Lexer $lexer): TypeNodeInterface
    {
        $start = $lexer->getToken();

        if ($this->skip($lexer, TokenKindEnum::BRACKET_L)) {
            $type = $this->parseTypeReference($lexer);

            $this->expect($lexer, TokenKindEnum::BRACKET_R);

            $type = new ListTypeNode([
                'type' => $type,
                'loc'  => $this->createLocation($lexer, $start),
            ]);
        } else {
            $type = $this->parseNamedType($lexer);
        }

        if ($this->skip($lexer, TokenKindEnum::BANG)) {
            return new NonNullTypeNode([
                'type' => $type,
                'loc'  => $this->createLocation($lexer, $start),
            ]);
        }

        return $type;
    }

    /**
     * @param Lexer $lexer
     * @return NamedTypeNode
     * @throws GraphQLError
     */
    protected function parseNamedType(Lexer $lexer): NamedTypeNode
    {
        $start = $lexer->getToken();

        return new NamedTypeNode([
            'name' => $this->parseName($lexer),
            'loc'  => $this->createLocation($lexer, $start),
        ]);
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
