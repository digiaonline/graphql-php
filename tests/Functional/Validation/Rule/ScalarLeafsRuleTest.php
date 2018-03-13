<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Test\Functional\Validation\noSubselectionAllowed;
use function Digia\GraphQL\Test\Functional\Validation\requiredSubselection;
use Digia\GraphQL\Validation\Rule\ScalarLeafsRule;
use function Digia\GraphQL\Language\dedent;

class ScalarLeafsRuleTest extends RuleTestCase
{
    public function testValidScalarSelection()
    {
        $this->expectPassesRule(
            new ScalarLeafsRule(),
            dedent('
            fragment scalarSelection on Dog {
              barks
            }
            ')
        );
    }

    public function testObjectTypeMissingSelection()
    {
        $this->expectFailsRule(
            new ScalarLeafsRule(),
            dedent('
            query directQueryOnObjectWithoutSubFields {
              human
            }
            '),
            [requiredSubselection('human', 'Human', [2, 3])]
        );
    }

    public function testInterfaceTypeMissingSelection()
    {
        $this->expectFailsRule(
            new ScalarLeafsRule(),
            dedent('
            {
              human { pets }
            }
            '),
            [requiredSubselection('pets', '[Pet]', [2, 11])]
        );
    }

    public function testValidScalarSelectionWithArguments()
    {
        $this->expectPassesRule(
            new ScalarLeafsRule(),
            dedent('
            fragment scalarSelectionWithArgs on Dog {
              doesKnowCommand(dogCommand: SIT)
            }
            ')
        );
    }

    public function testScalarSelectionNotAllowedOnBoolean()
    {
        $this->expectFailsRule(
            new ScalarLeafsRule(),
            dedent('
            fragment scalarSelectionsNotAllowedOnBoolean on Dog {
              barks { sinceWhen }
            }
            '),
            [noSubselectionAllowed('barks', 'Boolean', [2, 9])]
        );
    }

    public function testScalarSelectionNotAllowedOnEnum()
    {
        $this->expectFailsRule(
            new ScalarLeafsRule(),
            dedent('
            fragment scalarSelectionsNotAllowedOnEnum on Cat {
              furColor { inHexdec }
            }
            '),
            [noSubselectionAllowed('furColor', 'FurColor', [2, 12])]
        );
    }

    public function testScalarSelectionNotAllowedWithArguments()
    {
        $this->expectFailsRule(
            new ScalarLeafsRule(),
            dedent('
            fragment scalarSelectionsNotAllowedWithArgs on Dog {
              doesKnowCommand(dogCommand: SIT) { sinceWhen }
            }
            '),
            [noSubselectionAllowed('doesKnowCommand', 'Boolean', [2, 36])]
        );
    }

    public function testScalarSelectionNotAllowedWithDirectives()
    {
        $this->expectFailsRule(
            new ScalarLeafsRule(),
            dedent('
            fragment scalarSelectionsNotAllowedWithDirectives on Dog {
              name @include(if: true) { isAlsoHumanName }
            }
            '),
            [noSubselectionAllowed('name', 'String', [2, 27])]
        );
    }

    public function testScalarSelectionNotAllowedWithDirectivesAndArguments()
    {
        $this->expectFailsRule(
            new ScalarLeafsRule(),
            dedent('
            fragment scalarSelectionsNotAllowedWithDirectivesAndArgs on Dog {
              doesKnowCommand(dogCommand: SIT) @include(if: true) { sinceWhen }
            }
            '),
            [noSubselectionAllowed('doesKnowCommand', 'Boolean', [2, 55])]
        );
    }
}
