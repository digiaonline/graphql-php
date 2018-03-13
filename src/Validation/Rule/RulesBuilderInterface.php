<?php

namespace Digia\GraphQL\Validation\Rule;

interface RulesBuilderInterface
{
    /**
     * @return RuleInterface[]
     */
    public function build(): array;
}
