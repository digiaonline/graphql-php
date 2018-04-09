<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class ASTBuilder implements ASTBuilderInterface
{
    use LexerUtilsTrait;

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    public function buildDocument(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::SOF);

        $definitions = [];

        do {
            $definitions[] = $this->buildDefinition($lexer);
        } while (!$this->skip($lexer, TokenKindEnum::EOF));

        return [
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => $definitions,
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array|null
     * @throws SyntaxErrorException
     */
    public function buildValueLiteral(LexerInterface $lexer, bool $isConst = false): ?array
    {
        return $this->parseValueLiteral($lexer, $isConst);
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    public function buildTypeReference(LexerInterface $lexer): ?array
    {
        return $this->parseTypeReference($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildOperationDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        if ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            return [
                'kind'                => NodeKindEnum::OPERATION_DEFINITION,
                'operation'           => 'query',
                'name'                => null,
                'variableDefinitions' => [],
                'directives'          => [],
                'selectionSet'        => $this->buildSelectionSet($lexer),
                'loc'                 => $this->buildLocation($lexer, $start),
            ];
        }

        $operation = $this->parseOperationType($lexer);

        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            $name = $this->buildName($lexer);
        }

        return [
            'kind'                => NodeKindEnum::OPERATION_DEFINITION,
            'operation'           => $operation,
            'name'                => $name ?? null,
            'variableDefinitions' => $this->buildVariableDefinitions($lexer),
            'directives'          => $this->buildDirectives($lexer),
            'selectionSet'        => $this->buildSelectionSet($lexer),
            'loc'                 => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildArguments(LexerInterface $lexer, bool $isConst = false): ?array
    {
        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::PAREN_L,
                [$this, $isConst ? 'buildConstArgument' : 'buildArgument'],
                TokenKindEnum::PAREN_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildArgument(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return mixed
         * @throws SyntaxErrorException
         */
        $buildValue = function (LexerInterface $lexer) {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->buildValueLiteral($lexer);
        };

        return [
            'kind'  => NodeKindEnum::ARGUMENT,
            'name'  => $this->buildName($lexer),
            'value' => $buildValue($lexer),
            'loc'   => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildConstArgument(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return mixed
         * @throws SyntaxErrorException
         */
        $buildValue = function (LexerInterface $lexer) {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->buildValueLiteral($lexer);
        };

        return [
            'kind'  => NodeKindEnum::ARGUMENT,
            'name'  => $this->buildName($lexer),
            'value' => $buildValue($lexer),
            'loc'   => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildArgumentsDefinition(LexerInterface $lexer): ?array
    {
        $buildFunction = function (LexerInterface $lexer): array {
            return $this->buildInputValueDefinition($lexer);
        };

        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::PAREN_L,
                $buildFunction,
                TokenKindEnum::PAREN_R
            )
            : [];
    }

    /**
     * @inheritdoc
     */
    public function buildDescription(LexerInterface $lexer): ?array
    {
        return $this->peekDescription($lexer)
            ? $this->buildStringLiteral($lexer)
            : null;
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function buildDirectiveDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::DIRECTIVE);
        $this->expect($lexer, TokenKindEnum::AT);

        $name      = $this->buildName($lexer);
        $arguments = $this->buildArgumentsDefinition($lexer);

        $this->expectKeyword($lexer, KeywordEnum::ON);

        $locations = $this->buildDirectiveLocations($lexer);

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
    protected function buildDirectiveLocations(LexerInterface $lexer): array
    {
        $this->skip($lexer, TokenKindEnum::PIPE);

        $locations = [];

        do {
            $locations[] = $this->buildDirectiveLocation($lexer);
        } while ($this->skip($lexer, TokenKindEnum::PIPE));

        return $locations;
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function buildDirectiveLocation(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $name = $this->buildName($lexer);

        if (\in_array($name['value'], DirectiveLocationEnum::values(), true)) {
            return $name;
        }

        throw $this->unexpected($lexer, $start);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildDirectives(LexerInterface $lexer, bool $isConst = false): ?array
    {
        $directives = [];

        while ($this->peek($lexer, TokenKindEnum::AT)) {
            $directives[] = $this->buildDirective($lexer, $isConst);
        }

        return $directives;
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildDirective(LexerInterface $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::AT);

        return [
            'kind'      => NodeKindEnum::DIRECTIVE,
            'name'      => $this->buildName($lexer),
            'arguments' => $this->buildArguments($lexer, $isConst),
            'loc'       => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function buildDefinition(LexerInterface $lexer): array
    {
        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            switch ($lexer->getTokenValue()) {
                case KeywordEnum::QUERY:
                case KeywordEnum::MUTATION:
                case KeywordEnum::SUBSCRIPTION:
                case KeywordEnum::FRAGMENT:
                    return $this->buildExecutableDefinition($lexer);
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
                    return $this->buildTypeSystemDefinition($lexer);
            }
        } elseif ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            return $this->buildExecutableDefinition($lexer);
        } elseif ($this->peekDescription($lexer)) {
            // Note: The schema definition language is an experimental addition.
            return $this->buildTypeSystemDefinition($lexer);
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildExecutableDefinition(LexerInterface $lexer): array
    {
        if ($this->peek($lexer, TokenKindEnum::NAME)) {
            // valid names are: query, mutation, subscription and fragment
            switch ($lexer->getToken()->getValue()) {
                case KeywordEnum::QUERY:
                case KeywordEnum::MUTATION:
                case KeywordEnum::SUBSCRIPTION:
                    return $this->buildOperationDefinition($lexer);
                case KeywordEnum::FRAGMENT:
                    return $this->buildFragmentDefinition($lexer);
            }
        } elseif ($this->peek($lexer, TokenKindEnum::BRACE_L)) {
            // Anonymous query
            return $this->buildOperationDefinition($lexer);
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     * @throws \ReflectionException
     */
    protected function buildTypeSystemDefinition(LexerInterface $lexer): array
    {
        // Many definitions begin with a description and require a lookahead.
        $keywordToken = $this->peekDescription($lexer) ? $lexer->lookahead() : $lexer->getToken();

        if ($keywordToken->getKind() === TokenKindEnum::NAME) {
            switch ($keywordToken->getValue()) {
                case KeywordEnum::SCHEMA:
                    return $this->buildSchemaDefinition($lexer);
                case KeywordEnum::SCALAR:
                    return $this->buildScalarTypeDefinition($lexer);
                case KeywordEnum::TYPE:
                    return $this->buildObjectTypeDefinition($lexer);
                case KeywordEnum::INTERFACE:
                    return $this->buildInterfaceTypeDefinition($lexer);
                case KeywordEnum::UNION:
                    return $this->buildUnionTypeDefinition($lexer);
                case KeywordEnum::ENUM:
                    return $this->buildEnumTypeDefinition($lexer);
                case KeywordEnum::INPUT:
                    return $this->buildInputObjectTypeDefinition($lexer);
                case KeywordEnum::DIRECTIVE:
                    return $this->buildDirectiveDefinition($lexer);
                case KeywordEnum::EXTEND:
                    return $this->buildTypeSystemExtension($lexer);
            }
        }

        throw $this->unexpected($lexer, $keywordToken);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildTypeSystemExtension(LexerInterface $lexer): array
    {
        $keywordToken = $lexer->lookahead();

        if ($keywordToken->getKind() === TokenKindEnum::NAME) {
            switch ($keywordToken->getValue()) {
                case KeywordEnum::SCALAR:
                    return $this->buildScalarTypeExtension($lexer, false);
                case KeywordEnum::TYPE:
                    return $this->buildObjectTypeExtension($lexer);
                case KeywordEnum::INTERFACE:
                    return $this->buildInterfaceTypeExtension($lexer);
                case KeywordEnum::UNION:
                    return $this->buildUnionTypeExtension($lexer);
                case KeywordEnum::ENUM:
                    return $this->buildEnumTypeExtension($lexer);
                case KeywordEnum::INPUT:
                    return $this->buildInputObjectTypeExtension($lexer);
            }
        }

        throw $this->unexpected($lexer, $keywordToken);
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildEnumTypeDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::ENUM);

        return [
            'kind'        => NodeKindEnum::ENUM_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $this->buildName($lexer),
            'directives'  => $this->buildDirectives($lexer),
            'values'      => $this->buildEnumValuesDefinition($lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildEnumTypeExtension(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::ENUM);

        $name       = $this->buildName($lexer);
        $directives = $this->buildDirectives($lexer);
        $values     = $this->buildEnumValuesDefinition($lexer);

        if (empty($directives) && empty($values)) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::ENUM_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'values'     => $values,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildEnumValuesDefinition(LexerInterface $lexer): ?array
    {
        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'buildEnumValueDefinition'],
                TokenKindEnum::BRACE_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildEnumValueDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        return [
            'kind'        => NodeKindEnum::ENUM_VALUE_DEFINITION,
            'description' => $this->buildDescription($lexer),
            'name'        => $this->buildName($lexer),
            'directives'  => $this->buildDirectives($lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildField(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $nameOrAlias = $this->buildName($lexer);

        if ($this->skip($lexer, TokenKindEnum::COLON)) {
            $alias = $nameOrAlias;
            $name  = $this->buildName($lexer);
        } else {
            $name = $nameOrAlias;
        }

        return [
            'kind'         => NodeKindEnum::FIELD,
            'alias'        => $alias ?? null,
            'name'         => $name,
            'arguments'    => $this->buildArguments($lexer, false),
            'directives'   => $this->buildDirectives($lexer),
            'selectionSet' => $this->peek($lexer, TokenKindEnum::BRACE_L)
                ? $this->buildSelectionSet($lexer)
                : null,
            'loc'          => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildFieldsDefinition(LexerInterface $lexer): ?array
    {
        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'buildFieldDefinition'],
                TokenKindEnum::BRACE_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildFieldDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $description = $this->buildDescription($lexer);
        $name        = $this->buildName($lexer);
        $arguments   = $this->buildArgumentsDefinition($lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        return [
            'kind'        => NodeKindEnum::FIELD_DEFINITION,
            'description' => $description,
            'name'        => $name,
            'arguments'   => $arguments,
            'type'        => $this->buildTypeReference($lexer),
            'directives'  => $this->buildDirectives($lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildFragment(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::SPREAD);

        $tokenValue = $lexer->getTokenValue();

        if ($tokenValue !== 'on' && $this->peek($lexer, TokenKindEnum::NAME)) {
            return [
                'kind'       => NodeKindEnum::FRAGMENT_SPREAD,
                'name'       => $this->buildFragmentName($lexer),
                'directives' => $this->buildDirectives($lexer),
                'loc'        => $this->buildLocation($lexer, $start),
            ];
        }

        if ($tokenValue === 'on') {
            $lexer->advance();

            $typeCondition = $this->buildNamedType($lexer);
        }

        return [
            'kind'          => NodeKindEnum::INLINE_FRAGMENT,
            'typeCondition' => $typeCondition ?? null,
            'directives'    => $this->buildDirectives($lexer),
            'selectionSet'  => $this->buildSelectionSet($lexer),
            'loc'           => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildFragmentDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::FRAGMENT);

        $buildTypeCondition = function (LexerInterface $lexer) {
            $this->expectKeyword($lexer, 'on');
            return $this->buildNamedType($lexer);
        };

        return [
            'kind'                => NodeKindEnum::FRAGMENT_DEFINITION,
            'name'                => $this->buildFragmentName($lexer),
            'variableDefinitions' => $this->buildVariableDefinitions($lexer),
            'typeCondition'       => $buildTypeCondition($lexer),
            'directives'          => $this->buildDirectives($lexer),
            'selectionSet'        => $this->buildSelectionSet($lexer),
            'loc'                 => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildFragmentName(LexerInterface $lexer): array
    {
        if ($lexer->getTokenValue() === 'on') {
            throw $this->unexpected($lexer);
        }

        return $this->buildName($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildImplementsInterfaces(LexerInterface $lexer): ?array
    {
        $types = [];

        if ($lexer->getTokenValue() === 'implements') {
            $lexer->advance();

            // Optional leading ampersand
            $this->skip($lexer, TokenKindEnum::AMP);

            do {
                $types[] = $this->buildNamedType($lexer);
            } while ($this->skip($lexer, TokenKindEnum::AMP));
        }

        return $types;
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildInputFieldsDefinition(LexerInterface $lexer): ?array
    {
        $buildFunction = function (LexerInterface $lexer): array {
            return $this->buildInputValueDefinition($lexer);
        };

        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                $buildFunction,
                TokenKindEnum::BRACE_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildInputObjectTypeDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::INPUT);

        return [
            'kind'        => NodeKindEnum::INPUT_OBJECT_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $this->buildName($lexer),
            'directives'  => $this->buildDirectives($lexer, true),
            'fields'      => $this->buildInputFieldsDefinition($lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildInputObjectTypeExtension(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::INPUT);

        $name       = $this->buildName($lexer);
        $directives = $this->buildDirectives($lexer, true);
        $fields     = $this->buildInputFieldsDefinition($lexer);

        if (empty($directives) && empty($fields)) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::INPUT_OBJECT_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'fields'     => $fields,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildInputValueDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildDescription($lexer);
        $name        = $this->buildName($lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        return [
            'kind'         => NodeKindEnum::INPUT_VALUE_DEFINITION,
            'description'  => $description,
            'name'         => $name,
            'type'         => $this->buildTypeReference($lexer),
            'defaultValue' => $this->skip($lexer, TokenKindEnum::EQUALS)
                ? $this->buildValueLiteral($lexer, true)
                : null,
            'directives'   => $this->buildDirectives($lexer, true),
            'loc'          => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildInterfaceTypeDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::INTERFACE);

        return [
            'kind'        => NodeKindEnum::INTERFACE_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $this->buildName($lexer),
            'directives'  => $this->buildDirectives($lexer),
            'fields'      => $this->buildFieldsDefinition($lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildInterfaceTypeExtension(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::INTERFACE);

        $name       = $this->buildName($lexer);
        $directives = $this->buildDirectives($lexer);
        $fields     = $this->buildFieldsDefinition($lexer);

        if (empty($directives) && empty($fields)) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::INTERFACE_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'fields'     => $fields,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildName(LexerInterface $lexer): ?array
    {
        $token = $this->expect($lexer, TokenKindEnum::NAME);

        return [
            'kind'  => NodeKindEnum::NAME,
            'value' => $token->getValue(),
            'loc'   => $this->buildLocation($lexer, $token),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildNamedType(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        return [
            'kind' => NodeKindEnum::NAMED_TYPE,
            'name' => $this->buildName($lexer),
            'loc'  => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildObjectTypeDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::TYPE);

        return [
            'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $this->buildName($lexer),
            'interfaces'  => $this->buildImplementsInterfaces($lexer),
            'directives'  => $this->buildDirectives($lexer),
            'fields'      => $this->buildFieldsDefinition($lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildObjectTypeExtension(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::TYPE);

        $name       = $this->buildName($lexer);
        $interfaces = $this->buildImplementsInterfaces($lexer);
        $directives = $this->buildDirectives($lexer);
        $fields     = $this->buildFieldsDefinition($lexer);

        if (empty($interfaces) && empty($directives) && empty($fields)) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::OBJECT_TYPE_EXTENSION,
            'name'       => $name,
            'interfaces' => $interfaces,
            'directives' => $directives,
            'fields'     => $fields,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildSchemaDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::SCHEMA);

        return [
            'kind'           => NodeKindEnum::SCHEMA_DEFINITION,
            'directives'     => $this->buildDirectives($lexer),
            'operationTypes' => $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'buildOperationTypeDefinition'],
                TokenKindEnum::BRACE_R
            ),
            'loc'            => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildOperationTypeDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        $operation = $this->parseOperationType($lexer);

        $this->expect($lexer, TokenKindEnum::COLON);

        return [
            'kind'      => NodeKindEnum::OPERATION_TYPE_DEFINITION,
            'operation' => $operation,
            'type'      => $this->buildNamedType($lexer),
            'loc'       => $this->buildLocation($lexer, $start),
        ];
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
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildSelectionSet(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        return [
            'kind'       => NodeKindEnum::SELECTION_SET,
            'selections' => $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                [$this, 'buildSelection'],
                TokenKindEnum::BRACE_R
            ),
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildSelection(LexerInterface $lexer): array
    {
        return $this->peek($lexer, TokenKindEnum::SPREAD)
            ? $this->buildFragment($lexer)
            : $this->buildField($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildScalarTypeDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::SCALAR);

        return [
            'kind'        => NodeKindEnum::SCALAR_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $this->buildName($lexer),
            'directives'  => $this->buildDirectives($lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildScalarTypeExtension(LexerInterface $lexer, bool $isConst = false): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::SCALAR);

        $name       = $this->buildName($lexer);
        $directives = $this->buildDirectives($lexer, $isConst);

        if (empty($directives)) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::SCALAR_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }

    protected function buildStringLiteral(LexerInterface $lexer): ?array
    {
        $token = $lexer->getToken();

        $lexer->advance();

        return [
            'kind'  => NodeKindEnum::STRING,
            'value' => $token->getValue(),
            'block' => $token->getKind() === TokenKindEnum::BLOCK_STRING,
            'loc'   => $this->buildLocation($lexer, $token),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseTypeReference(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        if ($this->skip($lexer, TokenKindEnum::BRACKET_L)) {
            $type = $this->buildTypeReference($lexer);

            $this->expect($lexer, TokenKindEnum::BRACKET_R);

            $type = [
                'kind' => NodeKindEnum::LIST_TYPE,
                'type' => $type,
                'loc'  => $this->buildLocation($lexer, $start),
            ];
        } else {
            $type = $this->buildNamedType($lexer);
        }

        if ($this->skip($lexer, TokenKindEnum::BANG)) {
            return [
                'kind' => NodeKindEnum::NON_NULL_TYPE,
                'type' => $type,
                'loc'  => $this->buildLocation($lexer, $start),
            ];
        }

        return $type;
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildUnionTypeDefinition(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $description = $this->buildDescription($lexer);

        $this->expectKeyword($lexer, KeywordEnum::UNION);

        return [
            'kind'        => NodeKindEnum::UNION_TYPE_DEFINITION,
            'description' => $description,
            'name'        => $this->buildName($lexer),
            'directives'  => $this->buildDirectives($lexer),
            'types'       => $this->buildUnionMemberTypes($lexer),
            'loc'         => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildUnionTypeExtension(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::UNION);

        $name       = $this->buildName($lexer);
        $directives = $this->buildDirectives($lexer);
        $types      = $this->buildUnionMemberTypes($lexer);

        if (empty($directives) && empty($types)) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::UNION_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'types'      => $types,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildUnionMemberTypes(LexerInterface $lexer): ?array
    {
        $types = [];

        if ($this->skip($lexer, TokenKindEnum::EQUALS)) {
            // Optional leading pipe
            $this->skip($lexer, TokenKindEnum::PIPE);

            do {
                $types[] = $this->buildNamedType($lexer);
            } while ($this->skip($lexer, TokenKindEnum::PIPE));
        }

        return $types;
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseValueLiteral(LexerInterface $lexer, bool $isConst): array
    {
        $token = $lexer->getToken();

        switch ($token->getKind()) {
            case TokenKindEnum::BRACKET_L:
                return $this->buildList($lexer, $isConst);
            case TokenKindEnum::BRACE_L:
                return $this->buildObject($lexer, $isConst);
            case TokenKindEnum::INT:
                $lexer->advance();

                return [
                    'kind'  => NodeKindEnum::INT,
                    'value' => $token->getValue(),
                    'loc'   => $this->buildLocation($lexer, $token),
                ];
            case TokenKindEnum::FLOAT:
                $lexer->advance();

                return [
                    'kind'  => NodeKindEnum::FLOAT,
                    'value' => $token->getValue(),
                    'loc'   => $this->buildLocation($lexer, $token),
                ];
            case TokenKindEnum::STRING:
            case TokenKindEnum::BLOCK_STRING:
                return $this->buildStringLiteral($lexer);
            case TokenKindEnum::NAME:
                $value = $token->getValue();

                if ($value === 'true' || $value === 'false') {
                    $lexer->advance();

                    return [
                        'kind'  => NodeKindEnum::BOOLEAN,
                        'value' => $value === 'true',
                        'loc'   => $this->buildLocation($lexer, $token),
                    ];
                }

                if ($value === 'null') {
                    $lexer->advance();

                    return [
                        'kind' => NodeKindEnum::NULL,
                        'loc'  => $this->buildLocation($lexer, $token),
                    ];
                }

                $lexer->advance();

                return [
                    'kind'  => NodeKindEnum::ENUM,
                    'value' => $token->getValue(),
                    'loc'   => $this->buildLocation($lexer, $token),
                ];
            case TokenKindEnum::DOLLAR:
                if (!$isConst) {
                    return $this->buildVariable($lexer);
                }
                break;
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildConstValue(LexerInterface $lexer): array
    {
        return $this->buildValueLiteral($lexer, true);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildValue(LexerInterface $lexer): array
    {
        return $this->buildValueLiteral($lexer, false);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildList(LexerInterface $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        return [
            'kind'   => NodeKindEnum::LIST,
            'values' => $this->any(
                $lexer,
                TokenKindEnum::BRACKET_L,
                [$this, $isConst ? 'buildConstValue' : 'buildValue'],
                TokenKindEnum::BRACKET_R
            ),
            'loc'    => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildObject(LexerInterface $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::BRACE_L);

        $fields = [];

        while (!$this->skip($lexer, TokenKindEnum::BRACE_R)) {
            $fields[] = $this->buildObjectField($lexer, $isConst);
        }

        return [
            'kind'   => NodeKindEnum::OBJECT,
            'fields' => $fields,
            'loc'    => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildObjectField(LexerInterface $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        $buildValue = function (LexerInterface $lexer, bool $isConst) {
            $this->expect($lexer, TokenKindEnum::COLON);

            return $this->buildValueLiteral($lexer, $isConst);
        };

        return [
            'kind'  => NodeKindEnum::OBJECT_FIELD,
            'name'  => $this->buildName($lexer),
            'value' => $buildValue($lexer, $isConst),
            'loc'   => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildVariable(LexerInterface $lexer): ?array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::DOLLAR);

        return [
            'kind' => NodeKindEnum::VARIABLE,
            'name' => $this->buildName($lexer),
            'loc'  => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array|null
     * @throws SyntaxErrorException
     */
    protected function buildVariableDefinitions(LexerInterface $lexer): ?array
    {
        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many($lexer, TokenKindEnum::PAREN_L, [$this, 'buildVariableDefinition'], TokenKindEnum::PAREN_R)
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function buildVariableDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return mixed
         * @throws SyntaxErrorException
         */
        $buildType = function (LexerInterface $lexer) {
            $this->expect($lexer, TokenKindEnum::COLON);

            return $this->buildTypeReference($lexer);
        };

        return [
            'kind'         => NodeKindEnum::VARIABLE_DEFINITION,
            'variable'     => $this->buildVariable($lexer),
            'type'         => $buildType($lexer),
            'defaultValue' => $this->skip($lexer, TokenKindEnum::EQUALS)
                ? $this->buildValueLiteral($lexer, true)
                : null,
            'loc'          => $this->buildLocation($lexer, $start),
        ];
    }
}
