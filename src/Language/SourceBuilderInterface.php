<?php

namespace Digia\GraphQL\Language;

/**
 * Interface SourceBuilderInterface
 * @package Digia\GraphQL\Language
 */
interface SourceBuilderInterface
{

    /**
     * @return Source
     */
    public function build(): Source;
}
