<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Error\formatError;
use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\AST\Visitor\VisitorBreak;
use function Digia\GraphQL\Test\Functional\Validation\testSchema;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Validation\ValidatorInterface;
use function Digia\GraphQL\parse;

abstract class RuleTestCase extends TestCase
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->validator = GraphQL::get(ValidatorInterface::class);
    }

    /**
     * @param $rule
     * @param $query
     * @throws \Digia\GraphQL\Language\AST\Visitor\VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    protected function expectPassesRule($rule, $query)
    {
        return $this->expectValid(testSchema(), [$rule], $query);
    }

    /**
     * @param $rule
     * @param $query
     * @param $expectedErrors
     * @return mixed
     * @throws VisitorBreak
     * @throws \TypeError
     * @throws \Exception
     */
    protected function expectFailsRule($rule, $query, $expectedErrors = [])
    {
        return $this->expectInvalid(testSchema(), [$rule], $query, $expectedErrors);
    }

    /**
     * @param $schema
     * @param $rules
     * @param $query
     * @throws VisitorBreak
     * @throws \Exception
     */
    protected function expectValid($schema, $rules, $query)
    {
        $errors = $this->validator->validate($schema, parse($query), $rules);
        $this->assertEmpty($errors, 'Should validate');
    }

    /**
     * @param $schema
     * @param $rules
     * @param $query
     * @param $expectedErrors
     * @return array|GraphQLError[]
     * @throws VisitorBreak
     * @throws \Exception
     */
    protected function expectInvalid($schema, $rules, $query, $expectedErrors): array
    {
        $errors = $this->validator->validate($schema, parse($query), $rules);
        $this->assertTrue(count($errors) >= 1, 'Should not validate');
        $this->assertEquals(array_map('Digia\GraphQL\Error\formatError', $errors), $expectedErrors);
        return $errors;
    }
}
