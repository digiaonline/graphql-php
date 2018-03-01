<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Builder\ArgumentBuilder;
use Digia\GraphQL\Language\AST\Builder\BooleanBuilder;
use Digia\GraphQL\Language\AST\Builder\DirectiveBuilder;
use Digia\GraphQL\Language\AST\Builder\DirectiveDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\DocumentBuilder;
use Digia\GraphQL\Language\AST\Builder\EnumBuilder;
use Digia\GraphQL\Language\AST\Builder\EnumTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\EnumTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\EnumValueDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\FieldBuilder;
use Digia\GraphQL\Language\AST\Builder\FieldDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\FloatBuilder;
use Digia\GraphQL\Language\AST\Builder\FragmentDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\FragmentSpreadBuilder;
use Digia\GraphQL\Language\AST\Builder\InlineFragmentBuilder;
use Digia\GraphQL\Language\AST\Builder\InputObjectTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\InputObjectTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\InputValueDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\IntBuilder;
use Digia\GraphQL\Language\AST\Builder\InterfaceTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\InterfaceTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\ListBuilder;
use Digia\GraphQL\Language\AST\Builder\ListTypeBuilder;
use Digia\GraphQL\Language\AST\Builder\NameBuilder;
use Digia\GraphQL\Language\AST\Builder\NamedTypeBuilder;
use Digia\GraphQL\Language\AST\Builder\NonNullTypeBuilder;
use Digia\GraphQL\Language\AST\Builder\NullBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectFieldBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\OperationDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\OperationTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\ScalarTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\ScalarTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\SchemaDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\SelectionSetBuilder;
use Digia\GraphQL\Language\AST\Builder\StringBuilder;
use Digia\GraphQL\Language\AST\Builder\UnionTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\UnionTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\VariableBuilder;
use Digia\GraphQL\Language\AST\Builder\VariableDefinitionBuilder;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\Reader\AmpReader;
use Digia\GraphQL\Language\Reader\AtReader;
use Digia\GraphQL\Language\Reader\BangReader;
use Digia\GraphQL\Language\Reader\BlockStringReader;
use Digia\GraphQL\Language\Reader\BraceReader;
use Digia\GraphQL\Language\Reader\BracketReader;
use Digia\GraphQL\Language\Reader\ColonReader;
use Digia\GraphQL\Language\Reader\CommentReader;
use Digia\GraphQL\Language\Reader\DollarReader;
use Digia\GraphQL\Language\Reader\EqualsReader;
use Digia\GraphQL\Language\Reader\NameReader;
use Digia\GraphQL\Language\Reader\NumberReader;
use Digia\GraphQL\Language\Reader\ParenthesisReader;
use Digia\GraphQL\Language\Reader\PipeReader;
use Digia\GraphQL\Language\Reader\SpreadReader;
use Digia\GraphQL\Language\Reader\StringReader;

/**
 * @return ParserInterface
 */
function parser(): ParserInterface
{
    static $instance = null;

    if (null === $instance) {
        $builders = [
            // Standard
            new ArgumentBuilder(),
            new BooleanBuilder(),
            new DirectiveBuilder(),
            new DocumentBuilder(),
            new EnumBuilder(),
            new FieldBuilder(),
            new FloatBuilder(),
            new FragmentDefinitionBuilder(),
            new FragmentSpreadBuilder(),
            new InlineFragmentBuilder(),
            new IntBuilder(),
            new ListBuilder(),
            new ListTypeBuilder(),
            new NameBuilder(),
            new NamedTypeBuilder(),
            new NonNullTypeBuilder(),
            new NullBuilder(),
            new ObjectBuilder(),
            new ObjectFieldBuilder(),
            new OperationDefinitionBuilder(),
            new SelectionSetBuilder(),
            new StringBuilder(),
            new VariableBuilder(),
            new VariableDefinitionBuilder(),
            // Schema Definition Language (SDL)
            new SchemaDefinitionBuilder(),
            new OperationTypeDefinitionBuilder(),
            new FieldDefinitionBuilder(),
            new ScalarTypeDefinitionBuilder(),
            new ObjectTypeDefinitionBuilder(),
            new InterfaceTypeDefinitionBuilder(),
            new UnionTypeDefinitionBuilder(),
            new EnumTypeDefinitionBuilder(),
            new EnumValueDefinitionBuilder(),
            new InputObjectTypeDefinitionBuilder(),
            new InputValueDefinitionBuilder(),
            new ScalarTypeExtensionBuilder(),
            new ObjectTypeExtensionBuilder(),
            new InterfaceTypeExtensionBuilder(),
            new EnumTypeExtensionBuilder(),
            new UnionTypeExtensionBuilder(),
            new InputObjectTypeExtensionBuilder(),
            new DirectiveDefinitionBuilder(),
        ];

        $readers = [
            new AmpReader(),
            new AtReader(),
            new BangReader(),
            new BlockStringReader(),
            new BraceReader(),
            new BracketReader(),
            new ColonReader(),
            new CommentReader(),
            new DollarReader(),
            new EqualsReader(),
            new NameReader(),
            new NumberReader(),
            new ParenthesisReader(),
            new PipeReader(),
            new SpreadReader(),
            new StringReader(),
        ];

        $instance = new Parser($builders, $readers);
    }

    return $instance;
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface
 * @throws GraphQLError
 * @throws \Exception
 */
function parse($source, array $options = [])
{
    return parser()
        ->parse($source instanceof Source ? $source : new Source($source), $options);
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface
 * @throws GraphQLError
 * @throws \Exception
 */
function parseValue($source, array $options = [])
{
    return parser()
        ->parseValue($source instanceof Source ? $source : new Source($source), $options);
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface
 * @throws GraphQLError
 * @throws \Exception
 */
function parseType($source, array $options = [])
{
    return parser()
        ->parseType($source instanceof Source ? $source : new Source($source), $options);
}
