<?php

use Digia\GraphQL\Language\AST\DirectiveLocationEnum;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\GraphQLDirective;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return Directive
 * @throws TypeError
 */
function GraphQLIncludeDirective(): Directive
{
    static $instance = null;

    if ($instance === null) {
        $instance = GraphQLDirective([
            'name'        => 'include',
            'description' =>
                'Directs the executor to include this field or fragment only when ' .
                'the `if` argument is true.',
            'locations'   => [
                DirectiveLocationEnum::FIELD,
                DirectiveLocationEnum::FRAGMENT_SPREAD,
                DirectiveLocationEnum::INLINE_FRAGMENT,
            ],
            'args'        => [
                'if ' => [
                    'type'        => GraphQLNonNull(GraphQLBoolean()),
                    'description' => 'Included when true.',
                ],
            ],
        ]);
    }

    return $instance;
}

/**
 * @return Directive
 * @throws TypeError
 */
function GraphQLSkipDirective(): Directive
{
    static $instance = null;

    if ($instance === null) {
        $instance = GraphQLDirective([
            'name'        => 'skip',
            'description' =>
                'Directs the executor to skip this field or fragment when the `if` ' .
                'argument is true.',
            'locations'   => [
                DirectiveLocationEnum::FIELD,
                DirectiveLocationEnum::FRAGMENT_SPREAD,
                DirectiveLocationEnum::INLINE_FRAGMENT,
            ],
            'args'        => [
                'if' => [
                    'type'        => GraphQLNonNull(GraphQLBoolean()),
                    'description' => 'Skipped when true.',
                ],
            ],
        ]);
    }

    return $instance;
}

const DEFAULT_DEPRECATION_REASON = 'No longer supported';

/**
 * @return Directive
 * @throws TypeError
 */
function GraphQLDeprecatedDirective(): Directive
{
    static $instance = null;

    if ($instance === null) {
        $instance = GraphQLDirective([
            'name'        => 'deprecated',
            'description' => 'Marks an element of a GraphQL schema as no longer supported.',
            'locations'   => [
                DirectiveLocationEnum::FIELD_DEFINITION,
                DirectiveLocationEnum::ENUM_VALUE,
            ],
            'args'        => [
                'reason' => [
                    'type'         => GraphQLString(),
                    'description'  =>
                        'Explains why this element was deprecated, usually also including a ' .
                        'suggestion for how to access supported similar data. Formatted ' .
                        'in [Markdown](https://daringfireball.net/projects/markdown/).',
                    'defaultValue' => DEFAULT_DEPRECATION_REASON,
                ],
            ]
        ]);
    }

    return $instance;
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
