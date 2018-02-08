<?php

namespace Digia\GraphQL\Type\Directive;

interface DirectiveInterface
{

    /**
     * @return array
     */
    public function getArguments(): array;
}
