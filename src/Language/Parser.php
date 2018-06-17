<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\BooleanValueNode;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\EnumTypeExtensionNode;
use Digia\GraphQL\Language\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FloatValueNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentNodeInterface;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InputObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\ListTypeNode;
use Digia\GraphQL\Language\Node\ListValueNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NonNullTypeNode;
use Digia\GraphQL\Language\Node\NullValueNode;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\ObjectValueNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\OperationTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ScalarTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ScalarTypeExtensionNode;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Language\Node\StringValueNode;
use Digia\GraphQL\Language\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\Language\Node\TypeExtensionNodeInterface;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Language\Node\UnionTypeDefinitionNode;
use Digia\GraphQL\Language\Node\UnionTypeExtensionNode;
use Digia\GraphQL\Language\Node\ValueNodeInterface;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use function Digia\GraphQL\Util\arraySome;

class Parser implements ParserInterface
{
    /**
     * @var LexerInterface
     */
    protected $lexer;

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     *
     * @throws InvariantException
     * @throws SyntaxErrorException
     */
    public function __call(string $name, array $arguments)
    {
        $lexCallback = str_replace('parse', 'lex', $name);

        if (\method_exists($this, $lexCallback)) {
            return $this->parsePartial([$this, $lexCallback], ...$arguments);
        }

        return $this;
    }

    /**
     * Given a GraphQL source, parses it into a Document.
     * Throws GraphQLError if a syntax error is encountered.
     *
     * @inheritdoc
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     * @throws InvariantException
     */
    public function parse($source, array $options = []): DocumentNode
    {
        $this->lexer = $this->createLexer($source, $options);

        return $this->lexDocument();
    }

    /**
     * @param callable      $lexCallback
     * @param Source|string $source
     * @param array         $options
     * @return mixed
     * @throws InvariantException
     * @throws SyntaxErrorException
     */
    protected function parsePartial(callable $lexCallback, $source, array $options = [])
    {
        $this->lexer = $this->createLexer($source, $options);

        $this->expect(TokenKindEnum::SOF);
        $node = \call_user_func($lexCallback, $source, $options);
        $this->expect(TokenKindEnum::EOF);

        return $node;
    }

    /**
     * Given a string containing a GraphQL value (ex. `[42]`), parse the AST for
     * that value.
     * Throws GraphQLError if a syntax error is encountered.
     *
     * This is useful within tools that operate upon GraphQL Values directly and
     * in isolation of complete GraphQL documents.
     *
     * @inheritdoc
     * @throws SyntaxErrorException
     * @throws InvariantException
     * @todo Deprecate this method in favor of `parsePartial`
     */
    public function parseValue($source, array $options = []): NodeInterface
    {
        $this->lexer = $this->createLexer($source, $options);

        $this->expect(TokenKindEnum::SOF);
        $value = $this->lexValueLiteral();
        $this->expect(TokenKindEnum::EOF);

        return $value;
    }

    /**
     * Given a string containing a GraphQL Type (ex. `[Int!]`), parse the AST for
     * that type.
     * Throws GraphQLError if a syntax error is encountered.
     *
     * This is useful within tools that operate upon GraphQL Types directly and
     * in isolation of complete GraphQL documents.
     *
     * @inheritdoc
     * @throws SyntaxErrorException
     * @throws InvariantException
     * @todo Deprecate this method in favor of `parsePartial`
     */
    public function parseType($source, array $options = []): TypeNodeInterface
    {
        $this->lexer = $this->createLexer($source, $options);

        $this->expect(TokenKindEnum::SOF);
        $type = $this->lexTypeReference();
        $this->expect(TokenKindEnum::EOF);

        return $type;
    }

    /**
     * Converts a name lex token into a name parse node.
     *
     * @return NameNode
     * @throws SyntaxErrorException
     */
    protected function lexName(): NameNode
    {
        $token = $this->expect(TokenKindEnum::NAME);

        return new NameNode($token->getValue(), $this->createLocation($token));
    }

    // Implements the parsing rules in the Document section.

    /**
     * Document : Definition+
     *
     * @return DocumentNode
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function lexDocument(): DocumentNode
    {
        $start = $this->lexer->getToken();

        $this->expect(TokenKindEnum::SOF);

        $definitions = [];

        do {
            $definitions[] = $this->lexDefinition();
        } while (!$this->skip(TokenKindEnum::EOF));

        return new DocumentNode($definitions, $this->createLocation($start));
    }

    /**
     * Definition :
     *   - ExecutableDefinition
     *   - TypeSystemDefinition
     *
     * @return NodeInterface
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function lexDefinition(): NodeInterface
    {
        if ($this->peek(TokenKindEnum::NAME)) {
            $token = $this->lexer->getToken();

            switch ($token->getValue()) {
                case KeywordEnum::QUERY:
                case KeywordEnum::MUTATION:
                case KeywordEnum::SUBSCRIPTION:
                case KeywordEnum::FRAGMENT:
                    return $this->lexExecutableDefinition();
                case KeywordEnum::SCHEMA:
                case KeywordEnum::SCALAR:
                case KeywordEnum::TYPE:
                case KeywordEnum::INTERFACE:
                case KeywordEnum::UNION:
                case KeywordEnum::ENUM:
                case KeywordEnum::INPUT:
                case KeywordEnum::EXTEND:
                case KeywordEnum::DIRECTIVE:
                    return $this->lexTypeSystemDefinition();
            }
        } elseif ($this->peek(TokenKindEnum::BRACE_L)) {
            return $this->lexExecutableDefinition();
        } elseif ($this->peekDescription()) {
            return $this->lexTypeSystemDefinition();
        }

        throw $this->unexpected();
    }

    /**
     * ExecutableDefinition :
     *   - OperationDefinition
     *   - FragmentDefinition
     *
     * @return NodeInterface
     * @throws SyntaxErrorException
     */
    protected function lexExecutableDefinition(): NodeInterface
    {
        if ($this->peek(TokenKindEnum::NAME)) {
            // Valid names are: query, mutation, subscription and fragment
            $token = $this->lexer->getToken();

            switch ($token->getValue()) {
                case KeywordEnum::QUERY:
                case KeywordEnum::MUTATION:
                case KeywordEnum::SUBSCRIPTION:
                    return $this->lexOperationDefinition();
                case KeywordEnum::FRAGMENT:
                    return $this->lexFragmentDefinition();
            }
        } elseif ($this->peek(TokenKindEnum::BRACE_L)) {
            // Anonymous query
            return $this->lexOperationDefinition();
        }

        throw $this->unexpected();
    }

    // Implements the parsing rules in the Operations section.

    /**
     * OperationDefinition :
     *  - SelectionSet
     *  - OperationType Name? VariableDefinitions? Directives? SelectionSet
     *
     * @return OperationDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexOperationDefinition(): OperationDefinitionNode
    {
        $start = $this->lexer->getToken();

        if ($this->peek(TokenKindEnum::BRACE_L)) {
            // Anonymous query
            return new OperationDefinitionNode(
                KeywordEnum::QUERY,
                null,
                [],
                [],
                $this->lexSelectionSet(),
                $this->createLocation($start)
            );
        }

        $operation = $this->lexOperationType();

        if ($this->peek(TokenKindEnum::NAME)) {
            $name = $this->lexName();
        }

        return new OperationDefinitionNode(
            $operation,
            $name ?? null,
            $this->lexVariableDefinitions(),
            $this->lexDirectives(),
            $this->lexSelectionSet(),
            $this->createLocation($start)
        );
    }

    /**
     * OperationType : one of query mutation subscription
     *
     * @return string
     * @throws SyntaxErrorException
     */
    protected function lexOperationType(): string
    {
        $token = $this->expect(TokenKindEnum::NAME);
        $value = $token->getValue();

        if (isOperation($value)) {
            return $value;
        }

        throw $this->unexpected($token);
    }

    /**
     * VariableDefinitions : ( VariableDefinition+ )
     *
     * @return array
     * @throws SyntaxErrorException
     */
    protected function lexVariableDefinitions(): array
    {
        return $this->peek(TokenKindEnum::PAREN_L)
            ? $this->many(
                TokenKindEnum::PAREN_L,
                [$this, 'lexVariableDefinition'],
                TokenKindEnum::PAREN_R
            )
            : [];
    }

    /**
     * VariableDefinition : Variable : Type DefaultValue?
     *
     * @return VariableDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexVariableDefinition(): VariableDefinitionNode
    {
        $start = $this->lexer->getToken();

        /**
         * @return TypeNodeInterface
         */
        $parseType = function (): TypeNodeInterface {
            $this->expect(TokenKindEnum::COLON);
            return $this->lexTypeReference();
        };

        return new VariableDefinitionNode(
            $this->lexVariable(),
            $parseType(),
            $this->skip(TokenKindEnum::EQUALS)
                ? $this->lexValueLiteral(true)
                : null,
            $this->createLocation($start)
        );
    }

    /**
     * Variable : $ Name
     *
     * @return VariableNode
     * @throws SyntaxErrorException
     */
    protected function lexVariable(): VariableNode
    {
        $start = $this->lexer->getToken();

        $this->expect(TokenKindEnum::DOLLAR);

        return new VariableNode($this->lexName(), $this->createLocation($start));
    }

    /**
     * SelectionSet : { Selection+ }
     *
     * @return SelectionSetNode
     * @throws SyntaxErrorException
     */
    protected function lexSelectionSet(): SelectionSetNode
    {
        $start = $this->lexer->getToken();

        return new SelectionSetNode(
            $this->many(
                TokenKindEnum::BRACE_L,
                [$this, 'lexSelection'],
                TokenKindEnum::BRACE_R
            ),
            $this->createLocation($start)
        );
    }

    /**
     * Selection :
     *   - Field
     *   - FragmentSpread
     *   - InlineFragment
     *
     * @return NodeInterface|FragmentNodeInterface|FieldNode
     * @throws SyntaxErrorException
     */
    protected function lexSelection(): NodeInterface
    {
        return $this->peek(TokenKindEnum::SPREAD)
            ? $this->lexFragment()
            : $this->lexField();
    }

    /**
     * Field : Alias? Name Arguments? Directives? SelectionSet?
     *
     * Alias : Name :
     *
     * @return FieldNode
     * @throws SyntaxErrorException
     */
    protected function lexField(): FieldNode
    {
        $start = $this->lexer->getToken();

        $nameOrAlias = $this->lexName();

        if ($this->skip(TokenKindEnum::COLON)) {
            $alias = $nameOrAlias;
            $name  = $this->lexName();
        } else {
            $name = $nameOrAlias;
        }

        return new FieldNode(
            $alias ?? null,
            $name,
            $this->lexArguments(false),
            $this->lexDirectives(),
            $this->peek(TokenKindEnum::BRACE_L)
                ? $this->lexSelectionSet()
                : null,
            $this->createLocation($start)
        );
    }

    /**
     * Arguments[Const] : ( Argument[?Const]+ )
     *
     * @param bool $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function lexArguments(bool $isConst = false): ?array
    {
        /**
         * @return ArgumentNode
         */
        $parseFunction = function () use ($isConst): ArgumentNode {
            return $this->lexArgument($isConst);
        };

        return $this->peek(TokenKindEnum::PAREN_L)
            ? $this->many(
                TokenKindEnum::PAREN_L,
                $parseFunction,
                TokenKindEnum::PAREN_R
            )
            : [];
    }

    /**
     * Argument[Const] : Name : Value[?Const]
     *
     * @param bool $isConst
     * @return ArgumentNode
     * @throws SyntaxErrorException
     */
    protected function lexArgument(bool $isConst = false): ArgumentNode
    {
        $start = $this->lexer->getToken();

        /**
         * @return NodeInterface|TypeNodeInterface|ValueNodeInterface
         */
        $parseValue = function () use ($isConst): NodeInterface {
            $this->expect(TokenKindEnum::COLON);
            return $this->lexValueLiteral($isConst);
        };

        return new ArgumentNode(
            $this->lexName(),
            $parseValue(),
            $this->createLocation($start)
        );
    }

    // Implements the parsing rules in the Fragments section.

    /**
     * Corresponds to both FragmentSpread and InlineFragment in the spec.
     *
     * FragmentSpread : ... FragmentName Directives?
     *
     * InlineFragment : ... TypeCondition? Directives? SelectionSet
     *
     * @return FragmentNodeInterface
     * @throws SyntaxErrorException
     */
    protected function lexFragment(): FragmentNodeInterface
    {
        $start = $this->lexer->getToken();

        $this->expect(TokenKindEnum::SPREAD);

        $token = $this->lexer->getToken();

        if (KeywordEnum::ON !== $token->getValue() && $this->peek(TokenKindEnum::NAME)) {
            return new FragmentSpreadNode(
                $this->lexFragmentName($token),
                $this->lexDirectives(),
                null,
                $this->createLocation($start)
            );
        }

        if (KeywordEnum::ON === $token->getValue()) {
            $this->lexer->advance();
            $typeCondition = $this->lexNamedType();
        }

        return new InlineFragmentNode(
            $typeCondition ?? null,
            $this->lexDirectives(),
            $this->lexSelectionSet(),
            $this->createLocation($start)
        );
    }

    /**
     * FragmentDefinition :
     *   - fragment FragmentName on TypeCondition Directives? SelectionSet
     *
     * TypeCondition : NamedType
     *
     * @return FragmentDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexFragmentDefinition(): FragmentDefinitionNode
    {
        $start = $this->lexer->getToken();

        $this->expectKeyword(KeywordEnum::FRAGMENT);

        $parseTypeCondition = function () {
            $this->expectKeyword(KeywordEnum::ON);
            return $this->lexNamedType();
        };

        return new FragmentDefinitionNode(
            $this->lexFragmentName(),
            $this->lexVariableDefinitions(),
            $parseTypeCondition(),
            $this->lexDirectives(),
            $this->lexSelectionSet(),
            $this->createLocation($start)
        );
    }

    /**
     * FragmentName : Name but not `on`
     *
     * @param Token|null $token
     * @return NameNode
     * @throws SyntaxErrorException
     */
    protected function lexFragmentName(?Token $token = null): NameNode
    {
        if (null === $token) {
            $token = $this->lexer->getToken();
        }

        if (KeywordEnum::ON === $token->getValue()) {
            throw $this->unexpected();
        }

        return $this->lexName();
    }

    // Implements the parsing rules in the Values section.

    /**
     * Value[Const] :
     *   - [~Const] Variable
     *   - IntValue
     *   - FloatValue
     *   - StringValue
     *   - BooleanValue
     *   - NullValue
     *   - EnumValue
     *   - ListValue[?Const]
     *   - ObjectValue[?Const]
     *
     * BooleanValue : one of `true` `false`
     *
     * NullValue : `null`
     *
     * EnumValue : Name but not `true`, `false` or `null`
     *
     * @param bool $isConst
     * @return NodeInterface|ValueNodeInterface|TypeNodeInterface
     * @throws SyntaxErrorException
     */
    protected function lexValueLiteral(bool $isConst = false): NodeInterface
    {
        $token = $this->lexer->getToken();
        $value = $token->getValue();

        switch ($token->getKind()) {
            case TokenKindEnum::BRACKET_L:
                return $this->lexList($isConst);
            case TokenKindEnum::BRACE_L:
                return $this->lexObject($isConst);
            case TokenKindEnum::INT:
                $this->lexer->advance();
                return new IntValueNode($value, $this->createLocation($token));
            case TokenKindEnum::FLOAT:
                $this->lexer->advance();
                return new FloatValueNode($value, $this->createLocation($token));
            case TokenKindEnum::STRING:
            case TokenKindEnum::BLOCK_STRING:
                return $this->lexStringLiteral();
            case TokenKindEnum::NAME:
                if ($value === 'true' || $value === 'false') {
                    $this->lexer->advance();
                    return new BooleanValueNode($value === 'true', $this->createLocation($token));
                }

                if ($value === 'null') {
                    $this->lexer->advance();
                    return new NullValueNode($this->createLocation($token));
                }

                $this->lexer->advance();
                return new EnumValueNode($value, $this->createLocation($token));
            case TokenKindEnum::DOLLAR:
                if (!$isConst) {
                    return $this->lexVariable();
                }
                break;
        }

        throw $this->unexpected();
    }

    /**
     * @return StringValueNode
     */
    protected function lexStringLiteral(): StringValueNode
    {
        $token = $this->lexer->getToken();

        $this->lexer->advance();

        return new StringValueNode(
            $token->getValue(),
            TokenKindEnum::BLOCK_STRING === $token->getKind(),
            $this->createLocation($token)
        );
    }

    /**
     * ListValue[Const] :
     *   - [ ]
     *   - [ Value[?Const]+ ]
     *
     * @param bool $isConst
     * @return ListValueNode
     * @throws SyntaxErrorException
     */
    protected function lexList(bool $isConst): ListValueNode
    {
        $start = $this->lexer->getToken();

        $parseFunction = function () use ($isConst) {
            return $this->lexValueLiteral($isConst);
        };

        return new ListValueNode(
            $this->any(
                TokenKindEnum::BRACKET_L,
                $parseFunction,
                TokenKindEnum::BRACKET_R
            ),
            $this->createLocation($start)
        );
    }

    /**
     * ObjectValue[Const] :
     *   - { }
     *   - { ObjectField[?Const]+ }
     *
     * @param bool $isConst
     * @return ObjectValueNode
     * @throws SyntaxErrorException
     */
    protected function lexObject(bool $isConst): ObjectValueNode
    {
        $start = $this->lexer->getToken();

        $this->expect(TokenKindEnum::BRACE_L);

        $fields = [];

        while (!$this->skip(TokenKindEnum::BRACE_R)) {
            $fields[] = $this->lexObjectField($isConst);
        }

        return new ObjectValueNode($fields, $this->createLocation($start));
    }

    /**
     * ObjectField[Const] : Name : Value[?Const]
     *
     * @param bool $isConst
     * @return ObjectFieldNode
     * @throws SyntaxErrorException
     */
    protected function lexObjectField(bool $isConst): ObjectFieldNode
    {
        $start = $this->lexer->getToken();

        /**
         * @param bool $isConst
         * @return NodeInterface|TypeNodeInterface|ValueNodeInterface
         */
        $parseValue = function (bool $isConst): NodeInterface {
            $this->expect(TokenKindEnum::COLON);
            return $this->lexValueLiteral($isConst);
        };

        return new ObjectFieldNode(
            $this->lexName(),
            $parseValue($isConst),
            $this->createLocation($start)
        );
    }

    // Implements the parsing rules in the Directives section.

    /**
     * Directives[Const] : Directive[?Const]+
     *
     * @param bool $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function lexDirectives(bool $isConst = false): array
    {
        $directives = [];

        while ($this->peek(TokenKindEnum::AT)) {
            $directives[] = $this->lexDirective($isConst);
        }

        return $directives;
    }

    /**
     * Directive[Const] : @ Name Arguments[?Const]?
     *
     * @param bool $isConst
     * @return DirectiveNode
     * @throws SyntaxErrorException
     */
    protected function lexDirective(bool $isConst): DirectiveNode
    {
        $start = $this->lexer->getToken();

        $this->expect(TokenKindEnum::AT);

        return new DirectiveNode(
            $this->lexName(),
            $this->lexArguments($isConst),
            $this->createLocation($start)
        );
    }

    // Implements the parsing rules in the Types section.

    /**
     * Type :
     *   - NamedType
     *   - ListType
     *   - NonNullType
     *
     * @return TypeNodeInterface
     * @throws SyntaxErrorException
     */
    protected function lexTypeReference(): TypeNodeInterface
    {
        $start = $this->lexer->getToken();

        if ($this->skip(TokenKindEnum::BRACKET_L)) {
            $type = $this->lexTypeReference();

            $this->expect(TokenKindEnum::BRACKET_R);

            $type = new ListTypeNode($type, $this->createLocation($start));
        } else {
            $type = $this->lexNamedType();
        }

        if ($this->skip(TokenKindEnum::BANG)) {
            return new NonNullTypeNode($type, $this->createLocation($start));
        }

        return $type;
    }

    /**
     * NamedType : Name
     *
     * @return NamedTypeNode
     * @throws SyntaxErrorException
     */
    protected function lexNamedType(): NamedTypeNode
    {
        $start = $this->lexer->getToken();

        return new NamedTypeNode($this->lexName(), $this->createLocation($start));
    }

    // Implements the parsing rules in the Type Definition section.

    /**
     * TypeSystemDefinition :
     *   - SchemaDefinition
     *   - TypeDefinition
     *   - TypeExtension
     *   - DirectiveDefinition
     *
     * TypeDefinition :
     *   - ScalarTypeDefinition
     *   - ObjectTypeDefinition
     *   - InterfaceTypeDefinition
     *   - UnionTypeDefinition
     *   - EnumTypeDefinition
     *   - InputObjectTypeDefinition
     *
     * @return NodeInterface|TypeDefinitionNodeInterface|TypeExtensionNodeInterface
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function lexTypeSystemDefinition(): NodeInterface
    {
        // Many definitions begin with a description and require a lookahead.
        $token = $this->peekDescription()
            ? $this->lexer->lookahead()
            : $this->lexer->getToken();

        if (TokenKindEnum::NAME === $token->getKind()) {
            switch ($token->getValue()) {
                case KeywordEnum::SCHEMA:
                    return $this->lexSchemaDefinition();
                case KeywordEnum::SCALAR:
                    return $this->lexScalarTypeDefinition();
                case KeywordEnum::TYPE:
                    return $this->lexObjectTypeDefinition();
                case KeywordEnum::INTERFACE:
                    return $this->lexInterfaceTypeDefinition();
                case KeywordEnum::UNION:
                    return $this->lexUnionTypeDefinition();
                case KeywordEnum::ENUM:
                    return $this->lexEnumTypeDefinition();
                case KeywordEnum::INPUT:
                    return $this->lexInputObjectTypeDefinition();
                case KeywordEnum::DIRECTIVE:
                    return $this->lexDirectiveDefinition();
                case KeywordEnum::EXTEND:
                    return $this->lexTypeSystemExtension();
            }
        }

        throw $this->unexpected($token);
    }

    /**
     * @return bool
     */
    protected function peekDescription(): bool
    {
        return $this->peek(TokenKindEnum::STRING) || $this->peek(TokenKindEnum::BLOCK_STRING);
    }

    /**
     * Description : StringValue
     *
     * @return StringValueNode|null
     */
    public function lexDescription(): ?StringValueNode
    {
        return $this->peekDescription()
            ? $this->lexStringLiteral()
            : null;
    }

    /**
     * SchemaDefinition : schema Directives[Const]? { OperationTypeDefinition+ }
     *
     * @return SchemaDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexSchemaDefinition(): SchemaDefinitionNode
    {
        $start = $this->lexer->getToken();

        $this->expectKeyword(KeywordEnum::SCHEMA);

        return new SchemaDefinitionNode(
            $this->lexDirectives(),
            $this->many(
                TokenKindEnum::BRACE_L,
                [$this, 'lexOperationTypeDefinition'],
                TokenKindEnum::BRACE_R
            ),
            $this->createLocation($start)
        );
    }

    /**
     * OperationTypeDefinition : OperationType : NamedType
     *
     * @return OperationTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexOperationTypeDefinition(): OperationTypeDefinitionNode
    {
        $start = $this->lexer->getToken();

        $operation = $this->lexOperationType();

        $this->expect(TokenKindEnum::COLON);

        return new OperationTypeDefinitionNode(
            $operation,
            $this->lexNamedType(),
            $this->createLocation($start)
        );
    }

    /**
     * ScalarTypeDefinition : Description? scalar Name Directives[Const]?
     *
     * @return ScalarTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexScalarTypeDefinition(): ScalarTypeDefinitionNode
    {
        $start = $this->lexer->getToken();

        $description = $this->lexDescription();

        $this->expectKeyword(KeywordEnum::SCALAR);

        return new ScalarTypeDefinitionNode(
            $description,
            $this->lexName(),
            $this->lexDirectives(),
            $this->createLocation($start)
        );
    }

    /**
     * ObjectTypeDefinition :
     *   Description?
     *   type Name ImplementsInterfaces? Directives[Const]? FieldsDefinition?
     *
     * @return ObjectTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexObjectTypeDefinition(): ObjectTypeDefinitionNode
    {
        $start = $this->lexer->getToken();

        $description = $this->lexDescription();

        $this->expectKeyword(KeywordEnum::TYPE);

        return new ObjectTypeDefinitionNode(
            $description,
            $this->lexName(),
            $this->lexImplementsInterfaces(),
            $this->lexDirectives(),
            $this->lexFieldsDefinition(),
            $this->createLocation($start)
        );
    }

    /**
     * ImplementsInterfaces :
     *   - implements `&`? NamedType
     *   - ImplementsInterfaces & NamedType
     *
     * @return array
     * @throws SyntaxErrorException
     */
    protected function lexImplementsInterfaces(): array
    {
        $types = [];

        $token = $this->lexer->getToken();

        if ('implements' === $token->getValue()) {
            $this->lexer->advance();

            // Optional leading ampersand
            $this->skip(TokenKindEnum::AMP);

            do {
                $types[] = $this->lexNamedType();
            } while ($this->skip(TokenKindEnum::AMP));
        }

        return $types;
    }

    /**
     * FieldsDefinition : { FieldDefinition+ }
     *
     * @return array
     * @throws SyntaxErrorException
     */
    protected function lexFieldsDefinition(): array
    {
        return $this->peek(TokenKindEnum::BRACE_L)
            ? $this->many(
                TokenKindEnum::BRACE_L,
                [$this, 'lexFieldDefinition'],
                TokenKindEnum::BRACE_R
            )
            : [];
    }

    /**
     * FieldDefinition :
     *   - Description? Name ArgumentsDefinition? : Type Directives[Const]?
     *
     * @return FieldDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexFieldDefinition(): FieldDefinitionNode
    {
        $start = $this->lexer->getToken();

        $description = $this->lexDescription();
        $name        = $this->lexName();
        $arguments   = $this->lexArgumentsDefinition();

        $this->expect(TokenKindEnum::COLON);

        return new FieldDefinitionNode(
            $description,
            $name,
            $arguments,
            $this->lexTypeReference(),
            $this->lexDirectives(),
            $this->createLocation($start)
        );
    }

    /**
     * ArgumentsDefinition : ( InputValueDefinition+ )
     *
     * @return InputValueDefinitionNode[]
     * @throws SyntaxErrorException
     */
    protected function lexArgumentsDefinition(): array
    {
        $parseFunction = function (): InputValueDefinitionNode {
            return $this->lexInputValueDefinition();
        };

        return $this->peek(TokenKindEnum::PAREN_L)
            ? $this->many(
                TokenKindEnum::PAREN_L,
                $parseFunction,
                TokenKindEnum::PAREN_R
            )
            : [];
    }

    /**
     * InputValueDefinition :
     *   - Description? Name : Type DefaultValue? Directives[Const]?
     *
     * @return InputValueDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexInputValueDefinition(): InputValueDefinitionNode
    {
        $start = $this->lexer->getToken();

        $description = $this->lexDescription();
        $name        = $this->lexName();

        $this->expect(TokenKindEnum::COLON);

        return new InputValueDefinitionNode(
            $description,
            $name,
            $this->lexTypeReference(),
            $this->skip(TokenKindEnum::EQUALS)
                ? $this->lexValueLiteral(true)
                : null,
            $this->lexDirectives(true),
            $this->createLocation($start)
        );
    }

    /**
     * InterfaceTypeDefinition :
     *   - Description? interface Name Directives[Const]? FieldsDefinition?
     *
     * @return InterfaceTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexInterfaceTypeDefinition(): InterfaceTypeDefinitionNode
    {
        $start = $this->lexer->getToken();

        $description = $this->lexDescription();

        $this->expectKeyword(KeywordEnum::INTERFACE);

        return new InterfaceTypeDefinitionNode(
            $description,
            $this->lexName(),
            $this->lexDirectives(),
            $this->lexFieldsDefinition(),
            $this->createLocation($start)
        );
    }

    /**
     * UnionTypeDefinition :
     *   - Description? union Name Directives[Const]? UnionMemberTypes?
     *
     * @return UnionTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexUnionTypeDefinition(): UnionTypeDefinitionNode
    {
        $start = $this->lexer->getToken();

        $description = $this->lexDescription();

        $this->expectKeyword(KeywordEnum::UNION);

        return new UnionTypeDefinitionNode(
            $description,
            $this->lexName(),
            $this->lexDirectives(),
            $this->lexUnionMemberTypes(),
            $this->createLocation($start)
        );
    }

    /**
     * UnionMemberTypes :
     *   - = `|`? NamedType
     *   - UnionMemberTypes | NamedType
     *
     * @return array
     * @throws SyntaxErrorException
     */
    protected function lexUnionMemberTypes(): array
    {
        $types = [];

        if ($this->skip(TokenKindEnum::EQUALS)) {
            // Optional leading pipe
            $this->skip(TokenKindEnum::PIPE);

            do {
                $types[] = $this->lexNamedType();
            } while ($this->skip(TokenKindEnum::PIPE));
        }

        return $types;
    }

    /**
     * EnumTypeDefinition :
     *   - Description? enum Name Directives[Const]? EnumValuesDefinition?
     *
     * @return EnumTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexEnumTypeDefinition(): EnumTypeDefinitionNode
    {
        $start = $this->lexer->getToken();

        $description = $this->lexDescription();

        $this->expectKeyword(KeywordEnum::ENUM);

        return new EnumTypeDefinitionNode(
            $description,
            $this->lexName(),
            $this->lexDirectives(),
            $this->lexEnumValuesDefinition(),
            $this->createLocation($start)
        );
    }

    /**
     * EnumValuesDefinition : { EnumValueDefinition+ }
     *
     * @return array
     * @throws SyntaxErrorException
     */
    protected function lexEnumValuesDefinition(): array
    {
        return $this->peek(TokenKindEnum::BRACE_L)
            ? $this->many(
                TokenKindEnum::BRACE_L,
                [$this, 'lexEnumValueDefinition'],
                TokenKindEnum::BRACE_R
            )
            : [];
    }

    /**
     * EnumValueDefinition : Description? EnumValue Directives[Const]?
     *
     * EnumValue : Name
     *
     * @return EnumValueDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexEnumValueDefinition(): EnumValueDefinitionNode
    {
        $start = $this->lexer->getToken();

        return new EnumValueDefinitionNode(
            $this->lexDescription(),
            $this->lexName(),
            $this->lexDirectives(),
            $this->createLocation($start)
        );
    }

    /**
     * InputObjectTypeDefinition :
     *   - Description? input Name Directives[Const]? InputFieldsDefinition?
     *
     * @return InputObjectTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function lexInputObjectTypeDefinition(): InputObjectTypeDefinitionNode
    {
        $start = $this->lexer->getToken();

        $description = $this->lexDescription();

        $this->expectKeyword(KeywordEnum::INPUT);

        return new InputObjectTypeDefinitionNode(
            $description,
            $this->lexName(),
            $this->lexDirectives(true),
            $this->lexInputFieldsDefinition(),
            $this->createLocation($start)
        );
    }

    /**
     * InputFieldsDefinition : { InputValueDefinition+ }
     *
     * @return array
     * @throws SyntaxErrorException
     */
    protected function lexInputFieldsDefinition(): array
    {
        $parseFunction = function (): InputValueDefinitionNode {
            return $this->lexInputValueDefinition();
        };

        return $this->peek(TokenKindEnum::BRACE_L)
            ? $this->many(
                TokenKindEnum::BRACE_L,
                $parseFunction,
                TokenKindEnum::BRACE_R
            )
            : [];
    }

    /**
     * TypeExtension :
     *   - ScalarTypeExtension
     *   - ObjectTypeExtension
     *   - InterfaceTypeExtension
     *   - UnionTypeExtension
     *   - EnumTypeExtension
     *   - InputObjectTypeDefinition
     *
     * @return TypeExtensionNodeInterface
     * @throws SyntaxErrorException
     */
    protected function lexTypeSystemExtension(): TypeExtensionNodeInterface
    {
        $token = $this->lexer->lookahead();

        if (TokenKindEnum::NAME === $token->getKind()) {
            switch ($token->getValue()) {
                case KeywordEnum::SCALAR:
                    return $this->lexScalarTypeExtension(false);
                case KeywordEnum::TYPE:
                    return $this->lexObjectTypeExtension();
                case KeywordEnum::INTERFACE:
                    return $this->lexInterfaceTypeExtension();
                case KeywordEnum::UNION:
                    return $this->lexUnionTypeExtension();
                case KeywordEnum::ENUM:
                    return $this->lexEnumTypeExtension();
                case KeywordEnum::INPUT:
                    return $this->lexInputObjectTypeExtension();
            }
        }

        throw $this->unexpected($token);
    }

    /**
     * ScalarTypeExtension :
     *   - extend scalar Name Directives[Const]
     *
     * @param bool $isConst
     * @return ScalarTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function lexScalarTypeExtension(bool $isConst = false): ScalarTypeExtensionNode
    {
        $start = $this->lexer->getToken();

        $this->expectKeyword(KeywordEnum::EXTEND);
        $this->expectKeyword(KeywordEnum::SCALAR);

        $name       = $this->lexName();
        $directives = $this->lexDirectives($isConst);

        if (empty($directives)) {
            throw $this->unexpected();
        }

        return new ScalarTypeExtensionNode($name, $directives, $this->createLocation($start));
    }

    /**
     * ObjectTypeExtension :
     *  - extend type Name ImplementsInterfaces? Directives[Const]? FieldsDefinition
     *  - extend type Name ImplementsInterfaces? Directives[Const]
     *  - extend type Name ImplementsInterfaces
     *
     * @return ObjectTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function lexObjectTypeExtension(): ObjectTypeExtensionNode
    {
        $start = $this->lexer->getToken();

        $this->expectKeyword(KeywordEnum::EXTEND);
        $this->expectKeyword(KeywordEnum::TYPE);

        $name       = $this->lexName();
        $interfaces = $this->lexImplementsInterfaces();
        $directives = $this->lexDirectives();
        $fields     = $this->lexFieldsDefinition();

        if (empty($interfaces) && empty($directives) && empty($fields)) {
            throw $this->unexpected();
        }

        return new ObjectTypeExtensionNode(
            $name,
            $interfaces,
            $directives,
            $fields,
            $this->createLocation($start)
        );
    }

    /**
     * InterfaceTypeExtension :
     *   - extend interface Name Directives[Const]? FieldsDefinition
     *   - extend interface Name Directives[Const]
     *
     * @return InterfaceTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function lexInterfaceTypeExtension(): InterfaceTypeExtensionNode
    {
        $start = $this->lexer->getToken();

        $this->expectKeyword(KeywordEnum::EXTEND);
        $this->expectKeyword(KeywordEnum::INTERFACE);

        $name       = $this->lexName();
        $directives = $this->lexDirectives();
        $fields     = $this->lexFieldsDefinition();

        if (empty($directives) && empty($fields)) {
            throw $this->unexpected();
        }

        return new InterfaceTypeExtensionNode($name, $directives, $fields, $this->createLocation($start));
    }

    /**
     * UnionTypeExtension :
     *   - extend union Name Directives[Const]? UnionMemberTypes
     *   - extend union Name Directives[Const]
     *
     * @return UnionTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function lexUnionTypeExtension(): UnionTypeExtensionNode
    {
        $start = $this->lexer->getToken();

        $this->expectKeyword(KeywordEnum::EXTEND);
        $this->expectKeyword(KeywordEnum::UNION);

        $name       = $this->lexName();
        $directives = $this->lexDirectives();
        $types      = $this->lexUnionMemberTypes();

        if (empty($directives) && empty($types)) {
            throw $this->unexpected();
        }

        return new UnionTypeExtensionNode($name, $directives, $types, $this->createLocation($start));
    }

    /**
     * EnumTypeExtension :
     *   - extend enum Name Directives[Const]? EnumValuesDefinition
     *   - extend enum Name Directives[Const]
     *
     * @return EnumTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function lexEnumTypeExtension(): EnumTypeExtensionNode
    {
        $start = $this->lexer->getToken();

        $this->expectKeyword(KeywordEnum::EXTEND);
        $this->expectKeyword(KeywordEnum::ENUM);

        $name       = $this->lexName();
        $directives = $this->lexDirectives();
        $values     = $this->lexEnumValuesDefinition();

        if (empty($directives) && empty($values)) {
            throw $this->unexpected();
        }

        return new EnumTypeExtensionNode($name, $directives, $values, $this->createLocation($start));
    }

    /**
     * InputObjectTypeExtension :
     *   - extend input Name Directives[Const]? InputFieldsDefinition
     *   - extend input Name Directives[Const]
     *
     * @return InputObjectTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function lexInputObjectTypeExtension(): InputObjectTypeExtensionNode
    {
        $start = $this->lexer->getToken();

        $this->expectKeyword(KeywordEnum::EXTEND);
        $this->expectKeyword(KeywordEnum::INPUT);

        $name       = $this->lexName();
        $directives = $this->lexDirectives(true);
        $fields     = $this->lexInputFieldsDefinition();

        if (empty($directives) && empty($fields)) {
            throw $this->unexpected();
        }

        return new InputObjectTypeExtensionNode($name, $directives, $fields, $this->createLocation($start));
    }

    /**
     * DirectiveDefinition :
     *   - Description? directive @ Name ArgumentsDefinition? on DirectiveLocations
     *
     * @return DirectiveDefinitionNode
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function lexDirectiveDefinition(): DirectiveDefinitionNode
    {
        $start = $this->lexer->getToken();

        $description = $this->lexDescription();

        $this->expectKeyword(KeywordEnum::DIRECTIVE);
        $this->expect(TokenKindEnum::AT);

        $name      = $this->lexName();
        $arguments = $this->lexArgumentsDefinition();

        $this->expectKeyword(KeywordEnum::ON);

        $locations = $this->lexDirectiveLocations();

        return new DirectiveDefinitionNode(
            $description,
            $name,
            $arguments,
            $locations,
            $this->createLocation($start)
        );
    }

    /**
     * DirectiveLocations :
     *   - `|`? DirectiveLocation
     *   - DirectiveLocations | DirectiveLocation
     *
     * @return array
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function lexDirectiveLocations(): array
    {
        $this->skip(TokenKindEnum::PIPE);

        $locations = [];

        do {
            $locations[] = $this->lexDirectiveLocation();
        } while ($this->skip(TokenKindEnum::PIPE));

        return $locations;
    }

    /**
     * DirectiveLocation :
     *   - ExecutableDirectiveLocation
     *   - TypeSystemDirectiveLocation
     *
     * ExecutableDirectiveLocation : one of
     *   `QUERY`
     *   `MUTATION`
     *   `SUBSCRIPTION`
     *   `FIELD`
     *   `FRAGMENT_DEFINITION`
     *   `FRAGMENT_SPREAD`
     *   `INLINE_FRAGMENT`
     *
     * TypeSystemDirectiveLocation : one of
     *   `SCHEMA`
     *   `SCALAR`
     *   `OBJECT`
     *   `FIELD_DEFINITION`
     *   `ARGUMENT_DEFINITION`
     *   `INTERFACE`
     *   `UNION`
     *   `ENUM`
     *   `ENUM_VALUE`
     *   `INPUT_OBJECT`
     *   `INPUT_FIELD_DEFINITION`
     *
     * @return NameNode
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function lexDirectiveLocation(): NameNode
    {
        $start = $this->lexer->getToken();

        $name = $this->lexName();

        if (arraySome(DirectiveLocationEnum::values(), function ($value) use ($name) {
            return $name->getValue() === $value;
        })) {
            return $name;
        }

        throw $this->unexpected($start);
    }

    /**
     * Returns a location object, used to identify the place in
     * the source that created a given parsed object.
     *
     * @param Token $start
     * @return Location|null
     */
    protected function createLocation(Token $start): ?Location
    {
        return !$this->lexer->getOption('noLocation', false)
            ? new Location(
                $start->getStart(),
                $this->lexer->getLastToken()->getEnd(),
                $this->lexer->getSource()
            )
            : null;
    }

    /**
     * @param string|Source $source
     * @param array         $options
     * @return LexerInterface
     * @throws InvariantException
     */
    protected function createLexer($source, array $options): LexerInterface
    {
        return new Lexer($source instanceof Source ? $source : new Source($source), $options);
    }

    /**
     * Determines if the next token is of a given kind.
     *
     * @param string $kind
     * @return bool
     */
    protected function peek(string $kind): bool
    {
        return $kind === $this->lexer->getToken()->getKind();
    }

    /**
     * If the next token is of the given kind, return true after advancing
     * the lexer. Otherwise, do not change the parser state and return false.
     *
     * @param string $kind
     * @return bool
     */
    protected function skip(string $kind): bool
    {
        if ($match = $this->peek($kind)) {
            $this->lexer->advance();
        }

        return $match;
    }

    /**
     * If the next token is of the given kind, return that token after advancing
     * the lexer. Otherwise, do not change the parser state and throw an error.
     *
     * @param string $kind
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function expect(string $kind): Token
    {
        $token = $this->lexer->getToken();

        if ($kind === $token->getKind()) {
            $this->lexer->advance();
            return $token;
        }

        throw $this->lexer->createSyntaxErrorException(\sprintf('Expected %s, found %s.', $kind, $token));
    }

    /**
     * @param string $value
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function expectKeyword(string $value): Token
    {
        $token = $this->lexer->getToken();

        if (TokenKindEnum::NAME === $token->getKind() && $value === $token->getValue()) {
            $this->lexer->advance();
            return $token;
        }

        throw $this->lexer->createSyntaxErrorException(\sprintf('Expected %s, found %s', $value, $token));
    }

    /**
     * Helper function for creating an error when an unexpected lexed token
     * is encountered.
     *
     * @param Token|null $atToken
     * @return SyntaxErrorException
     */
    protected function unexpected(?Token $atToken = null): SyntaxErrorException
    {
        $token = $atToken ?? $this->lexer->getToken();

        return $this->lexer->createSyntaxErrorException(\sprintf('Unexpected %s', $token));
    }

    /**
     * Returns a possibly empty list of parse nodes, determined by
     * the parseFn. This list begins with a lex token of openKind
     * and ends with a lex token of closeKind. Advances the parser
     * to the next lex token after the closing token.
     *
     * @param string   $openKind
     * @param callable $parseFunction
     * @param string   $closeKind
     * @return array
     * @throws SyntaxErrorException
     */
    protected function any(string $openKind, callable $parseFunction, string $closeKind): array
    {
        $this->expect($openKind);

        $nodes = [];

        while (!$this->skip($closeKind)) {
            $nodes[] = $parseFunction();
        }

        return $nodes;
    }

    /**
     * Returns a non-empty list of parse nodes, determined by
     * the parseFn. This list begins with a lex token of openKind
     * and ends with a lex token of closeKind. Advances the parser
     * to the next lex token after the closing token.
     *
     * @param string   $openKind
     * @param callable $parseFunction
     * @param string   $closeKind
     * @return array
     * @throws SyntaxErrorException
     */
    protected function many(string $openKind, callable $parseFunction, string $closeKind): array
    {
        $this->expect($openKind);

        $nodes = [$parseFunction()];

        while (!$this->skip($closeKind)) {
            $nodes[] = $parseFunction();
        }

        return $nodes;
    }
}
