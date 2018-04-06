<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\LoneAnonymousOperationRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\anonymousOperationNotAlone;

class LoneAnonymousOperationRuleTest extends RuleTestCase
{

    public function testNoOperations()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment fragA on Type {
              field
            }
            ')
        );
    }

    public function testOneAnonymousOperation()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field
            }
            ')
        );
    }

    public function testMultipleNamedOperations()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Foo {
              field
            }
            
            query Bar {
              field
            }
            ')
        );
    }

    public function testAnonymousOperationWithFragment()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              ...Foo
            }
            fragment Foo on Type {
              field
            }
            ')
        );
    }

    public function testMultipleAnonymousOperations()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              fieldA
            }
            {
              fieldB
            }            
            '),
            [
                anonymousOperationNotAlone([1, 1]),
                anonymousOperationNotAlone([4, 1]),
            ]
        );
    }

    public function testAnonymousOperationWithAMutation()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              fieldA
            }
            mutation Foo {
              fieldB
            }            
            '),
            [anonymousOperationNotAlone([1, 1])]
        );
    }

    public function testAnonymousOperationWithASubscription()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              fieldA
            }
            subscription Foo {
              fieldB
            }            
            '),
            [anonymousOperationNotAlone([1, 1])]
        );
    }

    protected function getRuleClassName(): string
    {
        return LoneAnonymousOperationRule::class;
    }
}
