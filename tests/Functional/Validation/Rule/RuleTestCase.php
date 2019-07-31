<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Validation\Rule\RuleInterface;
use Digia\GraphQL\Validation\ValidatorInterface;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Test\Functional\Validation\testSchema;

abstract class RuleTestCase extends TestCase
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var RuleInterface
     */
    protected $rule;

    abstract protected function getRuleClassName(): string;

    public function setUp()
    {
        $this->validator = GraphQL::make(ValidatorInterface::class);
        $this->rule      = GraphQL::make($this->getRuleClassName());
    }

    protected function expectPassesRule($rule, $query)
    {
        return $this->expectPassesRuleWithSchema(testSchema(), $rule, $query);
    }

    protected function expectPassesRuleWithSchema($schema, $rule, $query)
    {
        return $this->expectValid($schema, [$rule], $query);
    }

    protected function expectFailsRule($rule, $query, $expectedErrors = [])
    {
        return $this->expectFailsRuleWithSchema(testSchema(), $rule, $query, $expectedErrors);
    }

    protected function expectFailsRuleWithSchema($schema, $rule, $query, $expectedErrors = [])
    {
        return $this->expectInvalid($schema, [$rule], $query, $expectedErrors);
    }

    protected function expectValid($schema, $rules, $query)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $errors = $this->validator->validate($schema, parse(dedent($query)), $rules);
        $this->assertEmpty($errors, 'Should validate');
    }

    protected function expectInvalid($schema, $rules, $query, $expectedErrors): array
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $errors = $this->validator->validate($schema, parse(dedent($query)), $rules);
        $this->assertTrue(count($errors) >= 1, 'Should not validate');
        $this->assertEquals($expectedErrors, array_map('Digia\GraphQL\Error\formatError', $errors));
        return $errors;
    }
}
