<?php

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return Directive
 */
function GraphQLIncludeDirective(): Directive
{
    return GraphQL::make('GraphQLIncludeDirective');
}

/**
 * @return Directive
 */
function GraphQLSkipDirective(): Directive
{
    return GraphQL::make('GraphQLSkipDirective');
}

const DEFAULT_DEPRECATION_REASON = 'No longer supported';

/**
 * @return Directive
 */
function GraphQLDeprecatedDirective(): Directive
{
    return GraphQL::make('GraphQLDeprecatedDirective');
}

/**
 * @return array
 */
function specifiedDirectives(): array
{
    return [
        GraphQLIncludeDirective(),
        GraphQLSkipDirective(),
        GraphQLDeprecatedDirective(),
    ];
}

/**
 * @param DirectiveInterface $directive
 * @return bool
 */
function isSpecifiedDirective(DirectiveInterface $directive): bool
{
    return arraySome(
        specifiedDirectives(),
        function (DirectiveInterface $specifiedDirective) use ($directive) {
            return $specifiedDirective->getName() === $directive->getName();
        }
    );
}
