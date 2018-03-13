<?php

namespace Digia\GraphQL\Validation\Rule;

class RulesBuilder implements RulesBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(): array
    {
        return SupportedRules::getNew();
    }
}
