<?php

namespace Digia\GraphQL\Test\Functional\Schema;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Schema\Schema;
use function Digia\GraphQL\buildSchema;
use function Digia\GraphQL\Test\readFileContents;

class BuildingTest extends TestCase
{
    public function testBuildsSchema()
    {
        $source = readFileContents(__DIR__ . '/../starWars.graphqls');

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema($source);

        $this->assertInstanceOf(Schema::class, $schema);
    }
}
