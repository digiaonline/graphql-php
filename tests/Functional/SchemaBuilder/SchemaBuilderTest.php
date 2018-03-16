<?php

namespace Digia\GraphQL\Test\Functional\SchemaBuilder;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\buildSchema;
use function Digia\GraphQL\Util\readFile;

class SchemaBuilderTest extends TestCase
{
    public function testBuildsSchema()
    {
        $source = readFile(__DIR__ . '/../starWars.graphqls');

        $schema = buildSchema($source);

        $this->assertInstanceOf(Schema::class, $schema);
    }
}
