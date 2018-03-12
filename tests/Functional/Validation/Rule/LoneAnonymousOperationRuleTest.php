<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\LoneAnonymousOperationRule;
use function Digia\GraphQL\Test\Functional\Validation\anonymousOperationNotAlone;

class LoneAnonymousOperationRuleTest extends RuleTestCase
{
    public function testNoOperations()
    {
        $this->expectPassesRule(
            new LoneAnonymousOperationRule(),
            '
fragment fragA on Type {
  field
}
'
        );
    }

    public function testOneAnonymousOperation()
    {
        $this->expectPassesRule(
            new LoneAnonymousOperationRule(),
            '
{
  field
}
'
        );
    }

    public function testMultipleNamedOperations()
    {
        $this->expectPassesRule(
            new LoneAnonymousOperationRule(),
            '
query Foo {
  field
}

query Bar {
  field
}
'
        );
    }

    public function testAnonymousOperationWithFragment()
    {
        $this->expectPassesRule(
            new LoneAnonymousOperationRule(),
            '
{
  ...Foo
}
fragment Foo on Type {
  field
}
'
        );
    }

    public function testMultipleAnonymousOperations()
    {
        $this->expectFailsRule(
            new LoneAnonymousOperationRule(),
            '
{
  fieldA
}
{
  fieldB
}            
',
            [
                anonymousOperationNotAlone([2, 1]),
                anonymousOperationNotAlone([5, 1]),
            ]
        );
    }

    public function testAnonymousOperationWithAMutation()
    {
        $this->expectFailsRule(
            new LoneAnonymousOperationRule(),
            '
{
  fieldA
}
mutation Foo {
  fieldB
}            
',
            [anonymousOperationNotAlone([2, 1])]
        );
    }

    public function testAnonymousOperationWithASubscription()
    {
        $this->expectFailsRule(
            new LoneAnonymousOperationRule(),
            '
{
  fieldA
}
subscription Foo {
  fieldB
}            
',
            [anonymousOperationNotAlone([2, 1])]
        );
    }
}
