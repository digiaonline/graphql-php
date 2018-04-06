<?php

namespace Digia\GraphQL\Type\Definition;

interface DirectiveInterface
{

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @return bool
     */
    public function hasArguments(): bool;

    /**
     * @return array
     */
    public function getArguments(): array;
}
