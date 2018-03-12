<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Test\Functional\Validation\singleFieldOnly;
use Digia\GraphQL\Validation\Rule\SingleFieldSubscriptionsRule;
use function Digia\GraphQL\Language\dedent;

class SingleFieldSubscriptionsRuleTest extends RuleTestCase
{
    public function testValidSubscription()
    {
        $this->expectPassesRule(
            new SingleFieldSubscriptionsRule(),
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
            new SingleFieldSubscriptionsRule(),
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
            new SingleFieldSubscriptionsRule(),
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
            new SingleFieldSubscriptionsRule(),
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
            new SingleFieldSubscriptionsRule(),
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
