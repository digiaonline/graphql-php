<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionContext;
use function Digia\GraphQL\Execution\coerceArgumentValues;
use Digia\GraphQL\Execution\ValuesHelper;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\String;

class ValueHelperTest extends TestCase
{
    /**
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvalidTypeException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testGetValues()
    {
        $schema = newSchema([
            'query' =>
                newObjectType([
                    'name'   => 'Greeting',
                    'fields' => [
                        'greeting' => [
                            'type' => String(),
                            'args' => [
                                'name' => [
                                    'type' => String(),
                                ]
                            ]
                        ]
                    ]
                ])
        ]);


        $documentNode = parse('query Hello($name: String) { Greeting(name: $name) }');
        $operation  = $documentNode->getDefinitions()[0];
        $node       = $documentNode->getDefinitions()[0]->getSelectionSet()->getSelections()[0];
        $definition = $schema->getQueryType()->getFields()['greeting'];

        $context = new ExecutionContext(
            $schema, [], null, null, ['name' => 'Han Solo'], null, $operation, []
        );

        $args = coerceArgumentValues($definition, $node, $context->getVariableValues());

        $this->assertEquals(['name' => 'Han Solo'], $args);
    }
}
