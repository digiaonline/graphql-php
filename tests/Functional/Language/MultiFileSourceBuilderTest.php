<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Language\MultiFileSourceBuilder;
use Digia\GraphQL\Test\TestCase;

/**
 * Class MultiFileSourceBuilderTest
 * @package Digia\GraphQL\Test\Functional\Language
 */
class MultiFileSourceBuilderTest extends TestCase
{

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\FileNotFoundException
     */
    public function testBuildSource(): void
    {
        $builder = new MultiFileSourceBuilder([
            __DIR__ . '/schema-kitchen-sink.graphqls',
            __DIR__ . '/../starWars.graphqls',
        ]);

        $source = $builder->build();

        $this->assertEquals(3747, $source->getBodyLength());
    }
}
