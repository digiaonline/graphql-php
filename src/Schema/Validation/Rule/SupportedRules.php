<?php

namespace Digia\GraphQL\Schema\Validation\Rule;

use Digia\GraphQL\GraphQL;

class SupportedRules
{
    /**
     * @var array
     */
    private static $supportedRules = [
        RootTypesRule::class,
        DirectivesRule::class,
        TypesRule::class,
    ];

    /**
     * Rules maintain state so they should always be re-instantiated.
     *
     * @return RuleInterface[]
     */
    public static function build(): array
    {
        $rules = [];

        foreach (self::$supportedRules as $className) {
            $rules[] = GraphQL::make($className);
        }

        return $rules;
    }
}
