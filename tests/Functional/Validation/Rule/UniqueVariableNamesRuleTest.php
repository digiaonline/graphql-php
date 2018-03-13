<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Test\Functional\Validation\duplicateVariable;
use Digia\GraphQL\Validation\Rule\UniqueVariableNamesRule;
use function Digia\GraphQL\Language\dedent;

class UniqueVariableNamesRuleTest extends RuleTestCase
{
    public function testUniqueVariableNames()
    {
        $this->expectPassesRule(
            new UniqueVariableNamesRule(),
            dedent('
            query A($x: Int, $y: String) { __typename }
            query B($x: String, $y: Int) { __typename }
            ')
        );
    }

    public function testDuplicateVariableNames()
    {
        $this->expectFailsRule(
            new UniqueVariableNamesRule(),
            dedent('
            query A($x: Int, $x: Int, $x: String) { __typename }
            query B($x: String, $x: Int) { __typename }
            query C($x: Int, $x: Int) { __typename }
            '),
            [
                duplicateVariable('x', [[1, 10], [1, 19]]),
                duplicateVariable('x', [[1, 10], [1, 28]]),
                duplicateVariable('x', [[2, 10], [2, 22]]),
                duplicateVariable('x', [[3, 10], [3, 19]]),
            ]
        );
    }
}
