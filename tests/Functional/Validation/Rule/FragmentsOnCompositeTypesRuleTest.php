<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Language\AST\Visitor\VisitorBreak;
use Digia\GraphQL\Validation\Rule\FragmentsOnCompositeTypesRule;
use function Digia\GraphQL\Validation\Rule\fragmentOnNonCompositeMessage;

/**
 * @param string $fragmentName
 * @param string $type
 * @param int    $line
 * @param int    $column
 * @return array
 */
function fragmentOnNonComposite(string $fragmentName, string $typeName, int $line, int $column): array
{
    return [
        'message'   => fragmentOnNonCompositeMessage($fragmentName, $typeName),
        // TODO: Add locations when support has been added to GraphQLError.
        'locations' => null, //[['line' => $line, 'column' => $column]],
        'path'      => null,
    ];
}

class FragmentsOnCompositeTypesRuleTest extends RuleTestCase
{
    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testObjectIsValidFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            '
            fragment validFragment on Dog {
              barks
            }
            '
        );
    }

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testInterfaceIsValidFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            '
            fragment validFragment on Pet {
              name
            }
            '
        );
    }

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testObjectIsValidInlineFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            '
            fragment validFragment on Pet {
              ... on Dog {
                barks
              }
            }
            '
        );
    }

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testInlineFragmentWithoutTypeIsValid()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            '
            fragment validFragment on Pet {
              ... {
                name
              }
            }
            '
        );
    }

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testUnionIsValidFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            '
            fragment validFragment on CatOrDog {
              __typename
            }
            '
        );
    }

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testScalarIsInvalidFragmentType()
    {
        $this->expectFailsRule(
            new FragmentsOnCompositeTypesRule(),
            '
            fragment scalarFragment on Boolean {
              bad
            }
            ',
            [fragmentOnNonComposite('scalarFragment', 'Boolean', 2, 34)]
        );
    }
}
