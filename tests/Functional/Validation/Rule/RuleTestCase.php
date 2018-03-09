<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Validation\ValidatorInterface;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Test\Functional\Validation\testSchema;

abstract class RuleTestCase extends TestCase
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function setUp()
    {
        $this->validator = GraphQL::get(ValidatorInterface::class);
    }

    protected function expectPassesRule($rule, $query)
    {
        return $this->expectValid(testSchema(), [$rule], $query);
    }

    protected function expectFailsRule($rule, $query, $expectedErrors = [])
    {
        return $this->expectInvalid(testSchema(), [$rule], $query, $expectedErrors);
    }

    protected function expectValid($schema, $rules, $query)
    {
        $errors = $this->validator->validate($schema, parse($query), $rules);
        $this->assertEmpty($errors, 'Should validate');
    }

    protected function expectInvalid($schema, $rules, $query, $expectedErrors): array
    {
        $errors = $this->validator->validate($schema, parse($query), $rules);
        $this->assertTrue(count($errors) >= 1, 'Should not validate');
        $this->assertEquals(array_map('Digia\GraphQL\Error\formatError', $errors), $expectedErrors);
        return $errors;
    }
}
