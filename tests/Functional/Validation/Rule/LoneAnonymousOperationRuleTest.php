<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Language\dedent;
use Digia\GraphQL\Validation\Rule\LoneAnonymousOperationRule;
use function Digia\GraphQL\Test\Functional\Validation\anonymousOperationNotAlone;

class LoneAnonymousOperationRuleTest extends RuleTestCase
{
    public function testNoOperations()
    {
        $this->expectPassesRule(
            new LoneAnonymousOperationRule(),
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
            new LoneAnonymousOperationRule(),
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
            new LoneAnonymousOperationRule(),
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
            new LoneAnonymousOperationRule(),
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
            new LoneAnonymousOperationRule(),
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
            new LoneAnonymousOperationRule(),
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
            new LoneAnonymousOperationRule(),
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
}
