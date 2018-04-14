<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\printNode;
use function Digia\GraphQL\Test\readFileContents;

class SchemaPrinterTest extends TestCase
{
    public function testDoesNotAlterAST()
    {
        // This test seems kind of dumb test, but it makes sure that our parser
        // can handle the kitchen sink schema.

        $kitchenSink = readFileContents(__DIR__ . '/schema-kitchen-sink.graphqls');

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse($kitchenSink);

        $astBefore = $ast->toJSON();

        printNode($ast);

        $this->assertEquals($ast->toJSON(), $astBefore);
    }
}
