<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionContext;
use function Digia\GraphQL\Execution\getArgumentValues;
use Digia\GraphQL\Execution\ValuesHelper;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class ValuesResolverTest extends TestCase
{
    /**
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvalidTypeException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
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
        $definition = $schema->getQueryType()->getFields()['greeting'];

        $context = new ExecutionContext(
            $schema, [], null, null, ['name' => 'Han Solo'], null, $operation, []
        );

        $args = getArgumentValues($definition, $node, $context->getVariableValues());

        $this->assertEquals(['name' => 'Han Solo'], $args);
    }
}
