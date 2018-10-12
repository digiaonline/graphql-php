<?php

namespace Digia\GraphQL\Test\Functional\Validation;

use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Validation\ValidationException;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Util\TypeInfo;
use Digia\GraphQL\Validation\Rule\SupportedRules;
use Digia\GraphQL\Validation\ValidatorInterface;
use function Digia\GraphQL\Error\formatError;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\parse;

class ValidationTest extends TestCase
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function setUp()
    {
        $this->validator = GraphQL::make(ValidatorInterface::class);
    }

    public function testValidatesQueries()
    {
        $errors = $this->validateQuery(
            testSchema(),
            dedent('
            query {
              catOrDog {
                ... on Cat {
                  furColor
                }
                ... on Dog {
                  isHouseTrained
                }
              }
            }
            ')
        );

        $this->assertEquals([], $errors, 'Should validate');
    }

    public function testDetectsBadScalarParse()
    {
        $errors = $this->validateQuery(
            testSchema(),
            dedent('
            query {
              invalidArg(arg: "bad value")
            }
            ')
        );

        $this->assertEquals([
            'locations' => [['line' => 2, 'column' => 19]],
            'message'   =>
                'Expected type Invalid, found "bad value"; Invalid scalar is always invalid: bad value',
            'path'      => null,
        ], formatError($errors[0]));
    }

    public function testValidatesUsingACustomTypeInfo()
    {
        $typeInfo = new TypeInfo(testSchema(), function () {
            return null;
        });

        $errors = $this->validateQuery(
            testSchema(),
            dedent('
            query {
              catOrDog {
                ... on Cat {
                  furColor
                }
                ... on Dog {
                  isHouseTrained
                }
              }
            }
            '),
            SupportedRules::build(),
            $typeInfo
        );

        $errorMessages = \array_map(function (ValidationException $ex) {
            return $ex->getMessage();
        }, $errors);

        $this->assertEquals([
            'Cannot query field "catOrDog" on type "QueryRoot". Did you mean "catOrDog"?',
            'Cannot query field "furColor" on type "Cat". Did you mean "furColor"?',
            'Cannot query field "isHouseTrained" on type "Dog". Did you mean "isHouseTrained"?'
        ], $errorMessages);
    }

    /**
     * @return GraphQLException[]
     */
    protected function validateQuery($schema, $query, $rules = null, $typeInfo = null)
    {
        return $this->validator->validate($schema, parse($query), $rules, $typeInfo);
    }
}
