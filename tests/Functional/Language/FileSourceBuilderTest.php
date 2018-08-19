<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Language\FileSourceBuilder;
use Digia\GraphQL\Test\TestCase;

/**
 * Class FileSourceBuilderTest
 * @package Digia\GraphQL\Test\Functional\Language
 */
class FileSourceBuilderTest extends TestCase
{

    /**
     * @expectedException \Digia\GraphQL\Error\FileNotFoundException
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testFileNotFound(): void
    {
        $builder = new FileSourceBuilder('/not/existing/file.graphqls');

        $builder->build();
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\FileNotFoundException
     */
    public function testSuccess(): void
    {
        $builder = new FileSourceBuilder(__DIR__ . '/schema-kitchen-sink.graphqls');

        $source = $builder->build();

        $this->assertGreaterThan(0, $source->getBodyLength());
    }
}
