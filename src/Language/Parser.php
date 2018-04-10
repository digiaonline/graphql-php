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
     * @inheritdoc
     * @return NodeInterface
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     * @throws InvariantException
     */
    public function parse($source, array $options = []): NodeInterface
    {
        return $this->parseDocument($this->createLexer($source, $options));
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     * @throws SyntaxErrorException
     * @throws InvariantException
     */
    public function parseValue($source, array $options = []): NodeInterface
    {
        $lexer = $this->createLexer($source, $options);

        $this->expect($lexer, TokenKindEnum::SOF);
        $value = $this->parseValueLiteral($lexer);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $value;
    }

    /**
     * @inheritdoc
     * @return NodeInterface
     * @throws SyntaxErrorException
     * @throws InvariantException
     */
    public function parseType($source, array $options = []): NodeInterface
    {
        $lexer = $this->createLexer($source, $options);

        $this->expect($lexer, TokenKindEnum::SOF);
        $type = $this->parseTypeReference($lexer);
        $this->expect($lexer, TokenKindEnum::EOF);

        return $type;
    }

    /**
     * @param LexerInterface $lexer
     * @return DocumentNode
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    public function parseDocument(LexerInterface $lexer): DocumentNode
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::SOF);

        $definitions = [];

        do {
            $definitions[] = $this->parseDefinition($lexer);
        } while (!$this->skip($lexer, TokenKindEnum::EOF));

        return new DocumentNode([
            'definitions' => $definitions,
            'location'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return OperationDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseOperationDefinition(LexerInterface $lexer): OperationDefinitionNode
    {
        $start = $lexer->getToken();

        if ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            // Anonymous operation
            return new OperationDefinitionNode([
                'operation'    => 'query',
                'selectionSet' => $this->parseSelectionSet($lexer),
                'location'     => $this->createLocation($lexer, $start),
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
            'directives'          => $this->parseDirectives($lexer),
            'selectionSet'        => $this->parseSelectionSet($lexer),
            'location'            => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseArguments(LexerInterface $lexer, bool $isConst = false): ?array
    {
        $parseFunction = function (LexerInterface $lexer) use ($isConst): ArgumentNode {
            return $this->parseArgument($lexer, $isConst);
        };

        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::PAREN_L,
                $parseFunction,
                TokenKindEnum::PAREN_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return ArgumentNode
     * @throws SyntaxErrorException
     */
    protected function parseArgument(LexerInterface $lexer, bool $isConst = false): ArgumentNode
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return NodeInterface|TypeNodeInterface|ValueNodeInterface
         */
        $parseValue = function (LexerInterface $lexer) use ($isConst): NodeInterface {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->parseValueLiteral($lexer, $isConst);
        };

        return new ArgumentNode([
            'name'     => $this->parseName($lexer),
            'value'    => $parseValue($lexer),
            'location' => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseArgumentsDefinition(LexerInterface $lexer): array
    {
        $parseFunction = function (LexerInterface $lexer): InputValueDefinitionNode {
            return $this->parseInputValueDefinition($lexer);
        };

        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::PAREN_L,
                $parseFunction,
                TokenKindEnum::PAREN_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return NodeInterface|null
     */
    public function parseDescription(LexerInterface $lexer): ?NodeInterface
    {
        return $this->peekDescription($lexer)
            ? $this->parseStringLiteral($lexer)
            : null;
    }

    /**
     * @param LexerInterface $lexer
     * @return DirectiveDefinitionNode
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function parseDirectiveDefinition(LexerInterface $lexer): DirectiveDefinitionNode
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::DIRECTIVE);
        $this->expect($lexer, TokenKindEnum::AT);

        $name      = $this->parseName($lexer);
        $arguments = $this->parseArgumentsDefinition($lexer);

        $this->expectKeyword($lexer, KeywordEnum::ON);

        $locations = $this->parseDirectiveLocations($lexer);

        return new DirectiveDefinitionNode([
            'description' => $description,
            'name'        => $name,
            'arguments'   => $arguments,
            'locations'   => $locations,
            'location'    => $this->createLocation($lexer, $start),
        ]);
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
     * @return NameNode
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function parseDirectiveLocation(LexerInterface $lexer): NameNode
    {
        $start = $lexer->getToken();

        $name = $this->parseName($lexer);

        if (arraySome(DirectiveLocationEnum::values(), function ($value) use ($name) {
            return $name->getValue() === $value;
        })) {
            return $name;
        }

        throw $this->unexpected($lexer, $start);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseDirectives(LexerInterface $lexer, bool $isConst = false): array
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
     * @return DirectiveNode
     * @throws SyntaxErrorException
     */
    protected function parseDirective(LexerInterface $lexer, bool $isConst): DirectiveNode
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::AT);

        return new DirectiveNode([
            'name'      => $this->parseName($lexer),
            'arguments' => $this->parseArguments($lexer, $isConst),
            'location'  => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return NodeInterface
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function parseDefinition(LexerInterface $lexer): NodeInterface
    {
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
     * @return NodeInterface
     * @throws SyntaxErrorException
     */
    protected function parseExecutableDefinition(LexerInterface $lexer): NodeInterface
    {
        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            // valid names are: query, mutation, subscription and fragment
            switch ($lexer->getToken()->getValue()) {
                case KeywordEnum::QUERY:
                case KeywordEnum::MUTATION:
                case KeywordEnum::SUBSCRIPTION:
                    return $this->parseOperationDefinition($lexer);
                case KeywordEnum::FRAGMENT:
                    return $this->parseFragmentDefinition($lexer);
            }
        } elseif ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            // Anonymous query
            return $this->parseOperationDefinition($lexer);
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return NodeInterface|TypeDefinitionNodeInterface|TypeExtensionNodeInterface
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function parseTypeSystemDefinition(LexerInterface $lexer): NodeInterface
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
                case KeywordEnum::DIRECTIVE:
                    return $this->parseDirectiveDefinition($lexer);
                case KeywordEnum::EXTEND:
                    return $this->parseTypeSystemExtension($lexer);
            }
        }

        throw $this->unexpected($lexer, $keywordToken);
    }

    /**
     * @param LexerInterface $lexer
     * @return TypeExtensionNodeInterface
     * @throws SyntaxErrorException
     */
    protected function parseTypeSystemExtension(LexerInterface $lexer): TypeExtensionNodeInterface
    {
        $keywordToken = $lexer->lookahead();

        if ($keywordToken->getKind() === TokenKindEnum::NAME) {
            switch ($keywordToken->getValue()) {
                case KeywordEnum::SCALAR:
                    return $this->parseScalarTypeExtension($lexer, false);
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
     * @return EnumTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseEnumTypeDefinition(LexerInterface $lexer): EnumTypeDefinitionNode
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::ENUM);

        return new EnumTypeDefinitionNode([
            'description' => $description,
            'name'        => $this->parseName($lexer),
            'directives'  => $this->parseDirectives($lexer),
            'values'      => $this->parseEnumValuesDefinition($lexer),
            'location'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return EnumTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function parseEnumTypeExtension(LexerInterface $lexer): EnumTypeExtensionNode
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::ENUM);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer);
        $values     = $this->parseEnumValuesDefinition($lexer);

        if (empty($directives) && empty($values)) {
            throw $this->unexpected($lexer);
        }

        return new EnumTypeExtensionNode([
            'name'       => $name,
            'directives' => $directives,
            'values'     => $values,
            'location'   => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseEnumValuesDefinition(LexerInterface $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'parseEnumValueDefinition'],
                TokenKindEnum::BRACE_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return EnumValueDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseEnumValueDefinition(LexerInterface $lexer): EnumValueDefinitionNode
    {
        $start = $lexer->getToken();

        return new EnumValueDefinitionNode([
            'description' => $this->parseDescription($lexer),
            'name'        => $this->parseName($lexer),
            'directives'  => $this->parseDirectives($lexer),
            'location'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return FieldNode
     * @throws SyntaxErrorException
     */
    protected function parseField(LexerInterface $lexer): FieldNode
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
            'directives'   => $this->parseDirectives($lexer),
            'selectionSet' => $this->peek($lexer, TokenKindEnum::BRACE_L)
                ? $this->parseSelectionSet($lexer)
                : null,
            'location'     => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseFieldsDefinition(LexerInterface $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'parseFieldDefinition'],
                TokenKindEnum::BRACE_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return FieldDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseFieldDefinition(LexerInterface $lexer): FieldDefinitionNode
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);
        $name        = $this->parseName($lexer);
        $arguments   = $this->parseArgumentsDefinition($lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        return new FieldDefinitionNode([
            'description' => $description,
            'name'        => $name,
            'arguments'   => $arguments,
            'type'        => $this->parseTypeReference($lexer),
            'directives'  => $this->parseDirectives($lexer),
            'location'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return FragmentNodeInterface
     * @throws SyntaxErrorException
     */
    protected function parseFragment(LexerInterface $lexer): FragmentNodeInterface
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::SPREAD);

        $tokenValue = $lexer->getTokenValue();

        if ($tokenValue !== 'on' && $this->peek($lexer, TokenKindEnum::NAME)) {
            return new FragmentSpreadNode([
                'name'       => $this->parseFragmentName($lexer),
                'directives' => $this->parseDirectives($lexer),
                'location'   => $this->createLocation($lexer, $start),
            ]);
        }

        if ($tokenValue === 'on') {
            $lexer->advance();
            $typeCondition = $this->parseNamedType($lexer);
        }

        return new InlineFragmentNode([
            'typeCondition' => $typeCondition ?? null,
            'directives'    => $this->parseDirectives($lexer),
            'selectionSet'  => $this->parseSelectionSet($lexer),
            'location'      => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return FragmentDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseFragmentDefinition(LexerInterface $lexer): FragmentDefinitionNode
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::FRAGMENT);

        $parseTypeCondition = function (LexerInterface $lexer) {
            $this->expectKeyword($lexer, 'on');
            return $this->parseNamedType($lexer);
        };

        return new FragmentDefinitionNode([
            'name'                => $this->parseFragmentName($lexer),
            'variableDefinitions' => $this->parseVariableDefinitions($lexer),
            'typeCondition'       => $parseTypeCondition($lexer),
            'directives'          => $this->parseDirectives($lexer),
            'selectionSet'        => $this->parseSelectionSet($lexer),
            'location'            => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return NameNode
     * @throws SyntaxErrorException
     */
    protected function parseFragmentName(LexerInterface $lexer): NameNode
    {
        if ($lexer->getTokenValue() === 'on') {
            throw $this->unexpected($lexer);
        }

        return $this->parseName($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
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
     * @throws SyntaxErrorException
     */
    protected function parseInputFieldsDefinition(LexerInterface $lexer): array
    {
        $parseFunction = function (LexerInterface $lexer): InputValueDefinitionNode {
            return $this->parseInputValueDefinition($lexer);
        };

        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                $parseFunction,
                TokenKindEnum::BRACE_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return InputObjectTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseInputObjectTypeDefinition(LexerInterface $lexer): InputObjectTypeDefinitionNode
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::INPUT);

        return new InputObjectTypeDefinitionNode([
            'description' => $description,
            'name'        => $this->parseName($lexer),
            'directives'  => $this->parseDirectives($lexer, true),
            'fields'      => $this->parseInputFieldsDefinition($lexer),
            'location'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return InputObjectTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function parseInputObjectTypeExtension(LexerInterface $lexer): InputObjectTypeExtensionNode
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::INPUT);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, true);
        $fields     = $this->parseInputFieldsDefinition($lexer);

        if (empty($directives) && empty($fields)) {
            throw $this->unexpected($lexer);
        }

        return new InputObjectTypeExtensionNode([
            'name'       => $name,
            'directives' => $directives,
            'fields'     => $fields,
            'location'   => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return InputValueDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseInputValueDefinition(LexerInterface $lexer): InputValueDefinitionNode
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);
        $name        = $this->parseName($lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        return new InputValueDefinitionNode([
            'description'  => $description,
            'name'         => $name,
            'type'         => $this->parseTypeReference($lexer),
            'defaultValue' => $this->skip($lexer, TokenKindEnum::EQUALS)
                ? $this->parseValueLiteral($lexer, true)
                : null,
            'directives'   => $this->parseDirectives($lexer, true),
            'location'     => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return InterfaceTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseInterfaceTypeDefinition(LexerInterface $lexer): InterfaceTypeDefinitionNode
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::INTERFACE);

        return new InterfaceTypeDefinitionNode([
            'description' => $description,
            'name'        => $this->parseName($lexer),
            'directives'  => $this->parseDirectives($lexer),
            'fields'      => $this->parseFieldsDefinition($lexer),
            'location'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return InterfaceTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function parseInterfaceTypeExtension(LexerInterface $lexer): InterfaceTypeExtensionNode
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::INTERFACE);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer);
        $fields     = $this->parseFieldsDefinition($lexer);

        if (empty($directives) && empty($fields)) {
            throw $this->unexpected($lexer);
        }

        return new InterfaceTypeExtensionNode([
            'name'       => $name,
            'directives' => $directives,
            'fields'     => $fields,
            'location'   => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return NamedTypeNode
     * @throws SyntaxErrorException
     */
    protected function parseNamedType(LexerInterface $lexer): NamedTypeNode
    {
        $start = $lexer->getToken();

        return new NamedTypeNode([
            'name'     => $this->parseName($lexer),
            'location' => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return NameNode
     * @throws SyntaxErrorException
     */
    protected function parseName(LexerInterface $lexer): NameNode
    {
        $token = $this->expect($lexer, TokenKindEnum::NAME);

        return new NameNode([
            'value'    => $token->getValue(),
            'location' => $this->createLocation($lexer, $token),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return ObjectTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseObjectTypeDefinition(LexerInterface $lexer): ObjectTypeDefinitionNode
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::TYPE);

        return new ObjectTypeDefinitionNode([
            'description' => $description,
            'name'        => $this->parseName($lexer),
            'interfaces'  => $this->parseImplementsInterfaces($lexer),
            'directives'  => $this->parseDirectives($lexer),
            'fields'      => $this->parseFieldsDefinition($lexer),
            'location'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return ObjectTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function parseObjectTypeExtension(LexerInterface $lexer): ObjectTypeExtensionNode
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::TYPE);

        $name       = $this->parseName($lexer);
        $interfaces = $this->parseImplementsInterfaces($lexer);
        $directives = $this->parseDirectives($lexer);
        $fields     = $this->parseFieldsDefinition($lexer);

        if (empty($interfaces) && empty($directives) && empty($fields)) {
            throw $this->unexpected($lexer);
        }

        return new ObjectTypeExtensionNode([
            'name'       => $name,
            'interfaces' => $interfaces,
            'directives' => $directives,
            'fields'     => $fields,
            'location'   => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return SchemaDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseSchemaDefinition(LexerInterface $lexer): SchemaDefinitionNode
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::SCHEMA);

        return new SchemaDefinitionNode([
            'directives'     => $this->parseDirectives($lexer),
            'operationTypes' => $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'parseOperationTypeDefinition'],
                TokenKindEnum::BRACE_R
            ),
            'location'       => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return OperationTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseOperationTypeDefinition(LexerInterface $lexer): OperationTypeDefinitionNode
    {
        $start = $lexer->getToken();

        $operation = $this->parseOperationType($lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        return new OperationTypeDefinitionNode([
            'operation' => $operation,
            'type'      => $this->parseNamedType($lexer),
            'location'  => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return string
     * @throws SyntaxErrorException
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
     * @return SelectionSetNode
     * @throws SyntaxErrorException
     */
    protected function parseSelectionSet(LexerInterface $lexer): SelectionSetNode
    {
        $start = $lexer->getToken();

        return new SelectionSetNode([
            'selections' => $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'parseSelection'],
                TokenKindEnum::BRACE_R
            ),
            'location'   => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return NodeInterface|FragmentNodeInterface|FieldNode
     * @throws SyntaxErrorException
     */
    protected function parseSelection(LexerInterface $lexer): NodeInterface
    {
        return $this->peek($lexer, TokenKindEnum::SPREAD)
            ? $this->parseFragment($lexer)
            : $this->parseField($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return ScalarTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseScalarTypeDefinition(LexerInterface $lexer): ScalarTypeDefinitionNode
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::SCALAR);

        return new ScalarTypeDefinitionNode([
            'description' => $description,
            'name'        => $this->parseName($lexer),
            'directives'  => $this->parseDirectives($lexer),
            'location'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return ScalarTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function parseScalarTypeExtension(LexerInterface $lexer, bool $isConst = false): ScalarTypeExtensionNode
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::SCALAR);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer, $isConst);

        if (empty($directives)) {
            throw $this->unexpected($lexer);
        }

        return new ScalarTypeExtensionNode([
            'name'       => $name,
            'directives' => $directives,
            'location'   => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return StringValueNode
     */
    protected function parseStringLiteral(LexerInterface $lexer): StringValueNode
    {
        $token = $lexer->getToken();

        $lexer->advance();

        return new StringValueNode([
            'value'    => $token->getValue(),
            'block'    => $token->getKind() === TokenKindEnum::BLOCK_STRING,
            'location' => $this->createLocation($lexer, $token),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return TypeNodeInterface
     * @throws SyntaxErrorException
     */
    protected function parseTypeReference(LexerInterface $lexer): TypeNodeInterface
    {
        $start = $lexer->getToken();

        if ($this->skip($lexer, TokenKindEnum::BRACKET_L)) {
            $type = $this->parseTypeReference($lexer);

            $this->expect($lexer, TokenKindEnum::BRACKET_R);

            $type = new ListTypeNode([
                'type'     => $type,
                'location' => $this->createLocation($lexer, $start),
            ]);
        } else {
            $type = $this->parseNamedType($lexer);
        }

        if ($this->skip($lexer, TokenKindEnum::BANG)) {
            return new NonNullTypeNode([
                'type'     => $type,
                'location' => $this->createLocation($lexer, $start),
            ]);
        }

        return $type;
    }

    /**
     * @param LexerInterface $lexer
     * @return UnionTypeDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseUnionTypeDefinition(LexerInterface $lexer): UnionTypeDefinitionNode
    {
        $start = $lexer->getToken();

        $description = $this->parseDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::UNION);

        return new UnionTypeDefinitionNode([
            'description' => $description,
            'name'        => $this->parseName($lexer),
            'directives'  => $this->parseDirectives($lexer),
            'types'       => $this->parseUnionMemberTypes($lexer),
            'location'    => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return UnionTypeExtensionNode
     * @throws SyntaxErrorException
     */
    protected function parseUnionTypeExtension(LexerInterface $lexer): UnionTypeExtensionNode
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::UNION);

        $name       = $this->parseName($lexer);
        $directives = $this->parseDirectives($lexer);
        $types      = $this->parseUnionMemberTypes($lexer);

        if (empty($directives) && empty($types)) {
            throw $this->unexpected($lexer);
        }

        return new UnionTypeExtensionNode([
            'name'       => $name,
            'directives' => $directives,
            'types'      => $types,
            'location'   => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
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
     * @param bool           $isConst
     * @return NodeInterface|ValueNodeInterface|TypeNodeInterface
     * @throws SyntaxErrorException
     */
    protected function parseValueLiteral(LexerInterface $lexer, bool $isConst = false): NodeInterface
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
                    'value'    => $token->getValue(),
                    'location' => $this->createLocation($lexer, $token),
                ]);
            case TokenKindEnum::FLOAT:
                $lexer->advance();

                return new FloatValueNode([
                    'value'    => $token->getValue(),
                    'location' => $this->createLocation($lexer, $token),
                ]);
            case TokenKindEnum::STRING:
            case TokenKindEnum::BLOCK_STRING:
                return $this->parseStringLiteral($lexer);
            case TokenKindEnum::NAME:
                $value = $token->getValue();

                if ($value === 'true' || $value === 'false') {
                    $lexer->advance();

                    return new BooleanValueNode([
                        'value'    => $value === 'true',
                        'location' => $this->createLocation($lexer, $token),
                    ]);
                }

                if ($value === 'null') {
                    $lexer->advance();

                    return new NullValueNode([
                        'location' => $this->createLocation($lexer, $token),
                    ]);
                }

                $lexer->advance();

                return new EnumValueNode([
                    'value'    => $token->getValue(),
                    'location' => $this->createLocation($lexer, $token),
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
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return ListValueNode
     * @throws SyntaxErrorException
     */
    protected function parseList(LexerInterface $lexer, bool $isConst): ListValueNode
    {
        $start = $lexer->getToken();

        $parseFunction = function (LexerInterface $lexer) use ($isConst) {
            return $this->parseValueLiteral($lexer, $isConst);
        };

        return new ListValueNode([
            'values'   => $this->any(
                $lexer,
                TokenKindEnum::BRACKET_L,
                $parseFunction,
                TokenKindEnum::BRACKET_R
            ),
            'location' => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return ObjectValueNode
     * @throws SyntaxErrorException
     */
    protected function parseObject(LexerInterface $lexer, bool $isConst): ObjectValueNode
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::BRACE_L);

        $fields = [];

        while (!$this->skip($lexer, TokenKindEnum::BRACE_R)) {
            $fields[] = $this->parseObjectField($lexer, $isConst);
        }

        return new ObjectValueNode([
            'fields'   => $fields,
            'location' => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return ObjectFieldNode
     * @throws SyntaxErrorException
     */
    protected function parseObjectField(LexerInterface $lexer, bool $isConst): ObjectFieldNode
    {
        $start = $lexer->getToken();

        $parseValue = function (LexerInterface $lexer, bool $isConst) {
            $this->expect($lexer, TokenKindEnum::COLON);

            return $this->parseValueLiteral($lexer, $isConst);
        };

        return new ObjectFieldNode([
            'name'     => $this->parseName($lexer),
            'value'    => $parseValue($lexer, $isConst),
            'location' => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return VariableNode
     * @throws SyntaxErrorException
     */
    protected function parseVariable(LexerInterface $lexer): VariableNode
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::DOLLAR);

        return new VariableNode([
            'name'     => $this->parseName($lexer),
            'location' => $this->createLocation($lexer, $start),
        ]);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseVariableDefinitions(LexerInterface $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::PAREN_L,
                [$this, 'parseVariableDefinition'],
                TokenKindEnum::PAREN_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return VariableDefinitionNode
     * @throws SyntaxErrorException
     */
    protected function parseVariableDefinition(LexerInterface $lexer): VariableDefinitionNode
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return mixed
         */
        $parseType = function (LexerInterface $lexer): TypeNodeInterface {
            $this->expect($lexer, TokenKindEnum::COLON);

            return $this->parseTypeReference($lexer);
        };

        return new VariableDefinitionNode([
            'variable'     => $this->parseVariable($lexer),
            'type'         => $parseType($lexer),
            'defaultValue' => $this->skip($lexer, TokenKindEnum::EQUALS)
                ? $this->parseValueLiteral($lexer, true)
                : null,
            'location'     => $this->createLocation($lexer, $start),
        ]);
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
     * @param LexerInterface $lexer
     * @param Token          $startToken
     * @return Location|null
     */
    protected function createLocation(LexerInterface $lexer, Token $startToken): ?Location
    {
        return !$lexer->getOption('noLocation', false)
            ? new Location($startToken->getStart(), $lexer->getLastToken()->getEnd(), $lexer->getSource())
            : null;
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
     * @throws SyntaxErrorException
     */
    protected function expect(LexerInterface $lexer, string $kind): Token
    {
        $token = $lexer->getToken();

        if ($token->getKind() === $kind) {
            $lexer->advance();
            return $token;
        }

        throw new SyntaxErrorException(
            $lexer->getSource(),
            $token->getStart(),
            sprintf('Expected %s, found %s.', $kind, $token)
        );
    }

    /**
     * @param LexerInterface $lexer
     * @param string         $value
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function expectKeyword(LexerInterface $lexer, string $value): Token
    {
        $token = $lexer->getToken();

        if ($token->getKind() === TokenKindEnum::NAME && $token->getValue() === $value) {
            $lexer->advance();
            return $token;
        }

        throw new SyntaxErrorException(
            $lexer->getSource(),
            $token->getStart(),
            sprintf('Expected %s, found %s', $value, $token)
        );
    }

    /**
     * Helper function for creating an error when an unexpected lexed token
     * is encountered.
     *
     * @param LexerInterface $lexer
     * @param Token|null     $atToken
     * @return SyntaxErrorException
     */
    protected function unexpected(LexerInterface $lexer, ?Token $atToken = null): SyntaxErrorException
    {
        $token = $atToken ?: $lexer->getToken();

        return new SyntaxErrorException(
            $lexer->getSource(),
            $token->getStart(),
            sprintf('Unexpected %s', $token)
        );
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
     * @throws SyntaxErrorException
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
     * @throws SyntaxErrorException
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
     * @return bool
     */
    protected function peekDescription(LexerInterface $lexer): bool
    {
        return $this->peek($lexer, TokenKindEnum::STRING) || $this->peek($lexer, TokenKindEnum::BLOCK_STRING);
    }
}
