<?php

namespace Digia\GraphQL\Type\Definition\Contract;

interface DirectiveInterface
{

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return bool
     */
    public function hasArgs(): bool;

    /**
     * @return array
     */
    public function getArgs(): array;
}
