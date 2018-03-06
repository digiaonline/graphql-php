<?php

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return Directive
 * @throws TypeError
 */
function GraphQLIncludeDirective(): Directive
{
    return GraphQL::get('GraphQLIncludeDirective');
}

/**
 * @return Directive
 * @throws TypeError
 */
function GraphQLSkipDirective(): Directive
{
    return GraphQL::get('GraphQLSkipDirective');
}

const DEFAULT_DEPRECATION_REASON = 'No longer supported';

/**
 * @return Directive
 * @throws TypeError
 */
function GraphQLDeprecatedDirective(): Directive
{
    return GraphQL::get('GraphQLDeprecatedDirective');
}

/**
 * @return array
 * @throws TypeError
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
 * @throws TypeError
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
