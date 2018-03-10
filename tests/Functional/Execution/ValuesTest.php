<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Execution\getArgumentValues;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class ValuesTest extends TestCase
{
    /**
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvalidTypeException
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testGetValues()
    {
        $schema = GraphQLSchema([
            'query' =>
                GraphQLObjectType([
                    'name'   => 'Greeting',
                    'fields' => [
                        'greeting' => [
                            'type' => GraphQLString(),
                            'args' => [
                                'name' => [
                                    'type' => GraphQLString(),
                                ]
                            ]
                        ]
                    ]
                ])
        ]);


        $documentNode = parse('query Hello($name: String) { Greeting(name: $name) }');
        $operation  = $documentNode->getDefinitions()[0];
        $node       = $documentNode->getDefinitions()[0]->getSelectionSet()->getSelections()[0];
        $definition = $schema->getQuery()->getFields()['greeting'];

        $context = new ExecutionContext(
            $schema, [], null, null, ['name' => 'Han Solo'], null, $operation, []
        );

        $args = getArgumentValues($definition, $node, $context->getVariableValues());

        $this->assertEquals(['name' => 'Han Solo'], $args);
    }
}