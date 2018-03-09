<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\buildSchema;
use function Digia\GraphQL\Util\readFile;

class SchemaBuilderTest extends TestCase
{

    public function testBuildsSchema()
    {
        $introspectionQuery = readFile(__DIR__ . '/schema-user-vote.graphqls');

        $schema = buildSchema($introspectionQuery);

        $this->assertInstanceOf(Schema::class, $schema);
    }
}
