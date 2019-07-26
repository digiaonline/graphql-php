<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;

class NonNullTest extends TestCase
{
    private $schemaWithNonNullArg;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithNonNullArg = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'withNonNullArg' => [
                        'type'    => stringType(),
                        'args'    => [
                            'cannotBeNull' => [
                                'type' => newNonNull(stringType()),
                            ],
                        ],
                        'resolve' => function ($_, $args) {
                            if (\is_string($args['cannotBeNull'])) {
                                return "Passed: {$args['cannotBeNull']}";
                            }
                        }
                    ],
                ],
            ])
        ]);
    }

    // Handles non-null argument

    public function testSucceedsWhenPassedNonNullLiteralValue()
    {
        $this->assertQueryResult(
            'query {
              withNonNullArg (cannotBeNull: "literal value")
            }',
            [
                'data' => [
                    'withNonNullArg' => 'Passed: literal value'
                ]
            ]
        );
    }

    public function testSucceedsWhenPassedNonNullVariableValue()
    {
        $this->assertQueryResult(
            'query ($testVar: String!) {
              withNonNullArg (cannotBeNull: $testVar)
            }',
            [
                'data' => [
                    'withNonNullArg' => 'Passed: variable value'
                ]
            ],
            ['testVar' => 'variable value']
        );
    }

    public function testSucceedsWhenMissingVariableHasDefaultValue()
    {
        $this->assertQueryResult(
            'query ($testVar: String = "default value") {
              withNonNullArg (cannotBeNull: $testVar)
            }',
            [
                'data' => [
                    'withNonNullArg' => 'Passed: default value'
                ]
            ],
            // Intentionally missing variable
            []
        );
    }

    public function testFieldErrorWhenMissingNonNullArg()
    {
        // Note: validation should identify this issue first (missing args rule)
        // however execution should still protect against this.

        $this->assertQueryResult(
            'query {
              withNonNullArg
            }',
            [
                'data'   => [
                    'withNonNullArg' => null,
                ],
                'errors' => [
                    [
                        'message'   => 'Argument "cannotBeNull" of required type "String!" was not provided.',
                        'locations' => [
                            [
                                'line'   => 2,
                                'column' => 15,
                            ],
                        ],
                        'path'      => ['withNonNullArg'],
                    ],
                ],
            ],
        );
    }

    public function testFieldErrorWhenNonNullArgProvidedNull()
    {
        // Note: validation should identify this issue first (values of correct
        // type rule) however execution should still protect against this.

        $this->markTestSkipped('Requires proper support for null values.');

        $this->assertQueryResult(
            'query {
              withNonNullArg(cannotBeNull: null)
            }',
            [
                'data'   => [
                    'withNonNullArg' => null,
                ],
                'errors' => [
                    [
                        'message'   => 'Argument "cannotBeNull" of non-null type "String!" must not be null.',
                        'locations' => [
                            [
                                'line'   => 2,
                                'column' => 44,
                            ],
                        ],
                        'path'      => ['withNonNullArg'],
                    ],
                ],
            ],
        );
    }

    public function testFieldErrorWhenNonNullArgNotProvidedVariableValue()
    {
        // Note: validation should identify this issue first (variables in allowed
        // position rule) however execution should still protect against this.

        $this->assertQueryResult(
            'query ($testVar: String) {
              withNonNullArg(cannotBeNull: $testVar)
            }',
            [
                'data'   => [
                    'withNonNullArg' => null,
                ],
                'errors' => [
                    [
                        'message'   => 'Argument "cannotBeNull" of required type "String!" was provided the variable '
                            . '"$testVar" which was not provided a runtime value.',
                        'locations' => [
                            [
                                'line'   => 2,
                                'column' => 44,
                            ],
                        ],
                        'path'      => ['withNonNullArg'],
                    ],
                ],
            ],
            // Intentionally missing variable
            []
        );
    }

    public function testFieldErrorWhenNonNullArgProvidedVariableWithExplicitNullValue()
    {
        $this->markTestSkipped('Requires proper support for null values.');

        $this->assertQueryResult(
            'query ($testVar: String = "default value") {
              withNonNullArg (cannotBeNull: $testVar)
            }',
            [
                'data'   => [
                    'withNonNullArg' => null,
                ],
                'errors' => [
                    [
                        'message'   => 'Argument "cannotBeNull" of non-null type "String!" must not be null.',
                        'locations' => [
                            [
                                'line'   => 2,
                                'column' => 44,
                            ],
                        ],
                        'path'      => ['withNonNullArg'],
                    ],
                ],
            ],
            ['testVar' => null]
        );
    }

    /**
     * @param string $query
     * @param array  $expected
     * @param array  $variables
     */
    private function assertQueryResult(string $query, array $expected, array $variables = []): void
    {
        $this->assertQueryResultWithSchema($this->schemaWithNonNullArg, $query, $expected, null, null, $variables);
    }
}