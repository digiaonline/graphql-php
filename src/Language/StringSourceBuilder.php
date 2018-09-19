<?php

namespace Digia\GraphQL\Language;

class StringSourceBuilder implements SourceBuilderInterface
{

    /**
     * @var string
     */
    private $body;

    /**
     * StringSourceBuilder constructor.
     * @param string $body
     */
    public function __construct(string $body)
    {
        $this->body = $body;
    }

    /**
     * @inheritdoc
     */
    public function build(): Source
    {
        return new Source($this->body);
    }
}
