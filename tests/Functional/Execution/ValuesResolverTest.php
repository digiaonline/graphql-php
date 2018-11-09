<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ValuesResolver;
use Digia\GraphQL\Language\Node\ArgumentsAwareInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\booleanType;
use function Digia\GraphQL\Type\newInputObjectType;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;

class ValuesResolverTest extends TestCase
{
    /**
     * @var ValuesResolver
     */
    private $valuesResolver;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->valuesResolver = new ValuesResolver();
    }

    /**
     * @throws \Digia\GraphQL\Error\InvalidTypeException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Execution\ExecutionException
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     */
    public function testCoerceArgumentValues()
    {
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Greeting',
                'fields' => [
                    'greeting' => [
                        'type' => stringType(),
                        'args' => [
                            'name' => [
                                'type' => stringType(),
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $documentNode = parse('query Hello($name: String) { Greeting(name: $name) }');
        $operation    = $documentNode->getDefinitions()[0];
        $node         = $operation->getSelectionSet()->getSelections()[0];
        $definition   = $schema->getQueryType()->getFields()['greeting'];

        $context = new ExecutionContext(
            $schema, [], null, null, ['name' => 'Han Solo'], null, $operation, []
        );

        $args = $this->valuesResolver->coerceArgumentValues($definition, $node, $context->getVariableValues());

        $this->assertSame(['name' => 'Han Solo'], $args);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     * @throws \Digia\GraphQL\Util\ConversionException
     */
    public function testCoerceVariableValues(): void
    {
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'nonNullBoolean',
                'fields' => [
                    'greeting' => [
                        'type' => stringType(),
                        'args' => [
                            'shout' => [
                                'type' => newNonNull(booleanType()),
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $documentNode = parse('
            query ($shout: Boolean!) {
                nonNullBoolean(shout: $shout)
            }
         ');

        /** @var OperationDefinitionNode $operation */
        $operation           = $documentNode->getDefinitions()[0];
        $variableDefinitions = $operation->getVariableDefinitions();

        // Try with true and false and null (null should give errors, the rest shouldn't)
        $coercedValue = $this->valuesResolver->coerceVariableValues($schema, $variableDefinitions, ['shout' => true]);
        $this->assertSame(['shout' => true], $coercedValue->getValue());
        $this->assertFalse($coercedValue->hasErrors());

        $coercedValue = $this->valuesResolver->coerceVariableValues($schema, $variableDefinitions, ['shout' => false]);
        $this->assertSame(['shout' => false], $coercedValue->getValue());
        $this->assertFalse($coercedValue->hasErrors());

        $coercedValue = $this->valuesResolver->coerceVariableValues($schema, $variableDefinitions, ['shout' => null]);
        $this->assertEquals([], $coercedValue->getValue());
        $this->assertTrue($coercedValue->hasErrors());
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     * @throws \Digia\GraphQL\Util\ConversionException
     */
    public function testCoerceValuesForInputObjectTypes(): void
    {
        // Test input object types
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'inputObjectField' => [
                        'type' => booleanType(),
                        'args' => [
                            'inputObject' => [
                                'type' => newInputObjectType([
                                        'name'   => 'InputObject',
                                        'fields' => [
                                            'a' => ['type' => stringType()],
                                            'b' => ['type' => newNonNull(stringType())]
                                        ]
                                    ]
                                )
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $documentNode = parse('
            query ($inputObject: InputObject!) {
                inputObjectField(inputObject: $inputObject)
            }
         ');

        /** @var OperationDefinitionNode $operation */
        $operation           = $documentNode->getDefinitions()[0];
        $variableDefinitions = $operation->getVariableDefinitions();

        // Test with a missing non-null string
        $coercedValue = $this->valuesResolver->coerceVariableValues($schema, $variableDefinitions, [
            'inputObject' => [
                'a' => 'some string'
            ]
        ]);

        $this->assertTrue($coercedValue->hasErrors());
        $this->assertEquals('Variable "$inputObject" got invalid value {"a":"some string"}; Field value.b of required type String! was not provided.',
            $coercedValue->getErrors()[0]->getMessage());

        // Test again with all variables, no errors expected
        $coercedValue = $this->valuesResolver->coerceVariableValues($schema, $variableDefinitions, [
            'inputObject' => [
                'a' => 'some string',
                'b' => 'some other required string',
            ]
        ]);

        $this->assertFalse($coercedValue->hasErrors());

        // Test with non-nullable boolean input fields
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'inputObjectField' => [
                        'type' => booleanType(),
                        'args' => [
                            'inputObject' => [
                                'type' => newInputObjectType([
                                        'name'   => 'InputObject',
                                        'fields' => [
                                            'a' => ['type' => booleanType()],
                                            'b' => ['type' => newNonNull(booleanType())]
                                        ]
                                    ]
                                )
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $documentNode = parse('
            query ($inputObject: InputObject!) {
                inputObjectField(inputObject: $inputObject)
            }
         ');

        /** @var OperationDefinitionNode $operation */
        $operation           = $documentNode->getDefinitions()[0];
        $variableDefinitions = $operation->getVariableDefinitions();

        // Test with a missing non-null string
        $coercedValue = $this->valuesResolver->coerceVariableValues($schema, $variableDefinitions, [
            'inputObject' => [
                'a' => true
            ]
        ]);

        $this->assertTrue($coercedValue->hasErrors());
        $this->assertEquals('Variable "$inputObject" got invalid value {"a":true}; Field value.b of required type Boolean! was not provided.',
            $coercedValue->getErrors()[0]->getMessage());

        // Test again with all fields present, all booleans true
        $coercedValue = $this->valuesResolver->coerceVariableValues($schema, $variableDefinitions, [
            'inputObject' => [
                'a' => true,
                'b' => true,
            ]
        ]);

        $this->assertFalse($coercedValue->hasErrors());

        // Test again with all fields present, all booleans false (this has been problematic before)
        $coercedValue = $this->valuesResolver->coerceVariableValues($schema, $variableDefinitions, [
            'inputObject' => [
                'a' => false,
                'b' => false,
            ]
        ]);

        $this->assertFalse($coercedValue->hasErrors());
    }
}
