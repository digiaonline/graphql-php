<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\UniqueOperationNamesRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\duplicateOperation;

class UniqueOperationNamesRuleTest extends RuleTestCase
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

    public function testOneNamedOperation()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Foo {
              field
            }
            ')
        );
    }

    public function testMultipleOperations()
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

    public function testMultipleOperationsOfDifferentTypes()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Foo {
              field
            }
            
            mutation Bar {
              field
            }
            
            subscription Baz {
              field
            }
            ')
        );
    }

    public function testFragmentAndOperationWithTheSameName()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Foo {
              ...Foo
            }
            
            fragment Foo on Type {
              field
            }
            ')
        );
    }

    public function testMultipleOperationWithTheSameName()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Foo {
              fieldA
            }
            
            query Foo {
              fieldB
            }
            '),
            [duplicateOperation('Foo', [[1, 7], [5, 7]])]
        );
    }

    public function testMultipleOperationsWithTheSameNameOfDifferentTypesMutation(
    )
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Foo {
              fieldA
            }
            
            mutation Foo {
              fieldB
            }
            '),
            [duplicateOperation('Foo', [[1, 7], [5, 10]])]
        );
    }

    public function testMultipleOperationsWithTheSameNameOfDifferentTypesSubscription(
    )
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Foo {
              fieldA
            }
            
            subscription Foo {
              fieldB
            }
            '),
            [duplicateOperation('Foo', [[1, 7], [5, 14]])]
        );
    }

    protected function getRuleClassName(): string
    {
        return UniqueOperationNamesRule::class;
    }
}
