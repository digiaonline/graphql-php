<?php

namespace Digia\GraphQL\Test\Functional\Error;

use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Error\printError;
use function Digia\GraphQL\parse;

class PrintErrorTest extends TestCase
{
    public function testPrintsAnErrorWithNodesFromDifferentSources()
    {
        $sourceA = parse(
            new Source(
                'type Foo {
  field: String
}
',
                'SourceA'
            )
        );

        $fieldTypeA = $sourceA->getDefinitions()[0]->getFields()[0]->getType();

        $sourceB = parse(
            new Source(
                'type Foo {
  field: Int
}
',
                'SourceB'
            )
        );

        $fieldTypeB = $sourceB->getDefinitions()[0]->getFields()[0]->getType();

        $error = new GraphQLException('Example error with two nodes', [
            $fieldTypeA,
            $fieldTypeB,
        ]);

        $this->assertEquals('Example error with two nodes

SourceA (2:10)
1: type Foo {
2:   field: String
            ^
3: }

SourceB (2:10)
1: type Foo {
2:   field: Int
            ^
3: }
', printError($error));
    }
}
