<?php

use Digia\GraphQL\GraphQLRuntime;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return Directive
 * @throws TypeError
 */
function GraphQLIncludeDirective(): Directive
{
    return GraphQLRuntime::get()->make('GraphQLIncludeDirective');
}

/**
 * @return Directive
 * @throws TypeError
 */
function GraphQLSkipDirective(): Directive
{
    return GraphQLRuntime::get()->make('GraphQLSkipDirective');
}

const DEFAULT_DEPRECATION_REASON = 'No longer supported';

/**
 * @return Directive
 * @throws TypeError
 */
function GraphQLDeprecatedDirective(): Directive
{
    return GraphQLRuntime::get()->make('GraphQLDeprecatedDirective');
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
 * @throws ReflectionException
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
