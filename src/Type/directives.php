<?php

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Type\Definition\Directive;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return Directive
 */
function IncludeDirective(): Directive
{
    return GraphQL::make(GraphQL::INCLUDE_DIRECTIVE);
}

/**
 * @return Directive
 */
function SkipDirective(): Directive
{
    return GraphQL::make(GraphQL::SKIP_DIRECTIVE);
}

const DEFAULT_DEPRECATION_REASON = 'No longer supported';

/**
 * @return Directive
 */
function DeprecatedDirective(): Directive
{
    return GraphQL::make(GraphQL::DEPRECATED_DIRECTIVE);
}

/**
 * @return array
 */
function specifiedDirectives(): array
{
    return [
        IncludeDirective(),
        SkipDirective(),
        DeprecatedDirective(),
    ];
}

/**
 * @param Directive $directive
 * @return bool
 */
function isSpecifiedDirective(Directive $directive): bool
{
    return arraySome(
        specifiedDirectives(),
        function (Directive $specifiedDirective) use ($directive) {
            return $specifiedDirective->getName() === $directive->getName();
        }
    );
}
