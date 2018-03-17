<?php

namespace Digia\GraphQL\Test\Functional\SchemaBuilder;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\buildSchema;
use function Digia\GraphQL\Test\readFileContents;

class SchemaBuilderTest extends TestCase
{
    public function testBuildsSchema()
    {
        $source = readFileContents(__DIR__ . '/../starWars.graphqls');

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema($source);

        $this->assertInstanceOf(Schema::class, $schema);
    }
}
