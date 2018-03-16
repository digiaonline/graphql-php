<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\SingleFieldSubscriptionsRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\singleFieldOnly;

class SingleFieldSubscriptionsRuleTest extends RuleTestCase
{
    protected function getRuleClassName(): string
    {
        return SingleFieldSubscriptionsRule::class;
    }

    public function testValidSubscription()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            subscription ImportantEmails {
              importantEmails
            }
            ')
        );
    }

    public function testFailsWithMoreThanOneRootField()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            subscription ImportantEmails {
              importantEmails
              notImportantEmails
            }
            '),
            [singleFieldOnly('ImportantEmails', [[3, 3]])]
        );
    }

    public function testFailsWithMoreThanOneRootFieldIncludingIntrospection()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            subscription ImportantEmails {
              importantEmails
              __typename
            }
            '),
            [singleFieldOnly('ImportantEmails', [[3, 3]])]
        );
    }

    public function testFailsWithManyMoreThanOneRootField()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            subscription ImportantEmails {
              importantEmails
              notImportantEmails
              spamEmails
            }
            '),
            [singleFieldOnly('ImportantEmails', [[3, 3], [4, 3]])]
        );
    }

    public function testFailsWithMoreThanOneRootFieldInAnonymousSubscription()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            subscription {
              importantEmails
              notImportantEmails
            }
            '),
            [singleFieldOnly(null, [[3, 3]])]
        );
    }
}
