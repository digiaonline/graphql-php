<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\printNode;
use function Digia\GraphQL\Util\readFile;

class SchemaPrinterTest extends TestCase
{

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testDoesNotAlterAST()
    {
        // This test seems kind of dumb test, but it makes sure that our parser
        // can handle the kitchen sink schema.

        $kitchenSink = readFile(__DIR__ . '/schema-kitchen-sink.graphqls');

        /** @var DocumentNode $ast */
        $ast       = parse($kitchenSink);
        $astBefore = $ast->toJSON();

        printNode($ast);

        $this->assertEquals($ast->toJSON(), $astBefore);
    }
}
