<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Language\StringSourceBuilder;
use Digia\GraphQL\Test\TestCase;

class StringSourceBuilderTest extends TestCase
{

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testBuild(): void
    {
        $body = \file_get_contents(__DIR__ . '/schema-kitchen-sink.graphqls');

        $builder = new StringSourceBuilder($body);
        $source  = $builder->build();

        $this->assertGreaterThan(0, $source->getBodyLength());
    }
}
