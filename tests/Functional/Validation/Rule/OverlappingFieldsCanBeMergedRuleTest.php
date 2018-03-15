<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\OverlappingFieldsCanBeMergedRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\fieldConflict;
use function Digia\GraphQL\Type\GraphQLID;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class OverlappingFieldsCanBeMergedRuleTest extends RuleTestCase
{
    protected $someBox;
    protected $stringBox;
    protected $intBox;
    protected $nonNullStringBox1;
    protected $nonNullStringBox1Impl;
    protected $nonNullStringBox2;
    protected $nonNullStringBox2Impl;
    protected $connection;
    protected $schema;

    protected function getRuleClassName(): string
    {
        return OverlappingFieldsCanBeMergedRule::class;
    }

    public function setUp()
    {
        parent::setUp();

        $this->someBox = GraphQLInterfaceType([
            'name'   => 'SomeBox',
            'fields' => function () {
                return [
                    'deepBox'        => ['type' => $this->someBox],
                    'unrelatedField' => ['type' => GraphQLString()],
                ];
            },
        ]);

        $this->stringBox = GraphQLObjectType([
            'name'       => 'StringBox',
            'interfaces' => [$this->someBox],
            'fields'     => function () {
                return [
                    'scalar'         => ['type' => GraphQLString()],
                    'deepBox'        => ['type' => $this->stringBox],
                    'unrelatedField' => ['type' => GraphQLString()],
                    'listStringBox'  => ['type' => GraphQLList($this->stringBox)],
                    'stringBox'      => ['type' => $this->stringBox],
                    'intBox'         => ['type' => $this->intBox],
                ];
            },
        ]);

        $this->intBox = GraphQLObjectType([
            'name'       => 'IntBox',
            'interfaces' => [$this->someBox],
            'fields'     => function () {
                return [
                    'scalar'         => ['type' => GraphQLInt()],
                    'deepBox'        => ['type' => $this->intBox],
                    'unrelatedField' => ['type' => GraphQLString()],
                    'listStringBox'  => ['type' => GraphQLList($this->stringBox)],
                    'stringBox'      => ['type' => $this->stringBox],
                    'intBox'         => ['type' => $this->intBox],
                ];
            },
        ]);

        $this->nonNullStringBox1 = GraphQLInterfaceType([
            'name'   => 'NonNullStringBox1',
            'fields' => [
                'scalar' => ['type' => GraphQLNonNull(GraphQLString())],
            ],
        ]);

        $this->nonNullStringBox1Impl = GraphQLObjectType([
            'name'       => 'NonNullStringBox1Impl',
            'interfaces' => [$this->someBox, $this->nonNullStringBox1],
            'fields'     => [
                'scalar'         => ['type' => GraphQLNonNull(GraphQLString())],
                'unrelatedField' => ['type' => GraphQLString()],
                'deepBox'        => ['type' => $this->someBox],
            ],
        ]);

        $this->nonNullStringBox2 = GraphQLInterfaceType([
            'name'   => 'NonNullStringBox2',
            'fields' => [
                'scalar' => ['type' => GraphQLNonNull(GraphQLString())],
            ],
        ]);

        $this->nonNullStringBox2Impl = GraphQLObjectType([
            'name'       => 'NonNullStringBox2Impl',
            'interfaces' => [$this->someBox, $this->nonNullStringBox2],
            'fields'     => [
                'scalar'         => ['type' => GraphQLNonNull(GraphQLString())],
                'unrelatedField' => ['type' => GraphQLString()],
                'deepBox'        => ['type' => $this->someBox],
            ],
        ]);

        $this->connection = GraphQLObjectType([
            'name'   => 'Connection',
            'fields' => function () {
                return [
                    'edges' => [
                        'type' => GraphQLList(
                            GraphQLObjectType([
                                'name'   => 'Edge',
                                'fields' => [
                                    'node' => [
                                        'type' => GraphQLObjectType([
                                            'name'   => 'Node',
                                            'fields' => [
                                                'id'   => ['type' => GraphQLID()],
                                                'name' => ['type' => GraphQLString()],
                                            ],
                                        ])
                                    ],
                                ],
                            ])
                        ),
                    ],
                ];
            },
        ]);

        $this->schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'QueryRoot',
                'fields' => function () {
                    return [
                        'someBox'    => ['type' => $this->someBox],
                        'connection' => ['type' => $this->connection],
                    ];
                },
            ]),
            'types' => [$this->intBox, $this->stringBox, $this->nonNullStringBox1Impl, $this->nonNullStringBox2Impl],
        ]);
    }

    public function testUniqueFields()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment uniqueFields on Dog {
              name
              nickname
            }
            ')
        );
    }

    public function testIdenticalFields()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment mergeIdenticalFields on Dog {
              name
              name
            }
            ')
        );
    }

    public function testIdenticalFieldsWithIdenticalArguments()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment mergeIdenticalFieldsWithIdenticalArgs on Dog {
              doesKnowCommand(dogCommand: SIT)
              doesKnowCommand(dogCommand: SIT)
            }
            ')
        );
    }

    public function testIdenticalFieldsWithIdenticalDirectives()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment mergeSameFieldsWithSameDirectives on Dog {
            name @include(if: true)
            name @include(if: true)
            }
            ')
        );
    }

    public function testDifferentArgumentsWithDifferentAliases()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment differentArgsWithDifferentAliases on Dog {
              knowsSit: doesKnowCommand(dogCommand: SIT)
              knowsDown: doesKnowCommand(dogCommand: DOWN)
            }
            ')
        );
    }

    public function testDifferentDirectivesWithDifferentAliases()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment differentDirectivesWithDifferentAliases on Dog {
              nameIfTrue: name @include(if: true)
              nameIfFalse: name @include(if: false)
            }
            ')
        );
    }

    public function testDifferentSkipIncludeDirectivesAccepted()
    {
        // Note: Differing skip/include directives don't create an ambiguous return
        // value and are acceptable in conditions where differing runtime values
        // may have the same desired effect of including or skipping a field.
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment differentDirectivesWithDifferentAliases on Dog {
              name @include(if: true)
              name @include(if: false)
            }
            ')
        );
    }

    public function testSameAliasesWithDifferentFieldTargets()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment sameAliasesWithDifferentFieldTargets on Dog {
              fido: name
              fido: nickname
            }
            '),
            [fieldConflict('fido', 'name and nickname are different fields', [[2, 3], [3, 3]])]
        );
    }

    public function testSameAliasesAllowedOnNonOverlappingFields()
    {
        // This is valid since no object can be both a "Dog" and a "Cat", thus
        // these fields can never overlap.
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment sameAliasesWithDifferentFieldTargets on Pet {
              ... on Dog {
                name
              }
              ... on Cat {
                name: nickname
              }
            }
            ')
        );
    }

    public function testAliasMaskingDirectFieldAccess()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment aliasMaskingDirectFieldAccess on Dog {
              name: nickname
              name
            }
            '),
            [fieldConflict('name', 'nickname and name are different fields', [[2, 3], [3, 3]])]
        );
    }

    public function testDifferentArgumentsSecondAddsAnArgument()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment conflictingArgs on Dog {
              doesKnowCommand
              doesKnowCommand(dogCommand: HEEL)
            }
            '),
            [fieldConflict('doesKnowCommand', 'they have differing arguments', [[2, 3], [3, 3]])]
        );
    }

    public function testDifferentArgsSecondMissingAnArgument()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment conflictingArgs on Dog {
              doesKnowCommand(dogCommand: SIT)
              doesKnowCommand
            }
            '),
            [fieldConflict('doesKnowCommand', 'they have differing arguments', [[2, 3], [3, 3]])]
        );
    }

    public function testConflictingArguments()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment conflictingArgs on Dog {
              doesKnowCommand(dogCommand: SIT)
              doesKnowCommand(dogCommand: HEEL)
            }
            '),
            [fieldConflict('doesKnowCommand', 'they have differing arguments', [[2, 3], [3, 3]])]
        );
    }

    public function testAllowsDifferentArgumentsWhereNoConflictIsPossible()
    {
        // This is valid since no object can be both a "Dog" and a "Cat", thus
        // these fields can never overlap.
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment conflictingArgs on Pet {
              ... on Dog {
                name(surname: true)
              }
              ... on Cat {
                name
              }
            }
            ')
        );
    }

    public function testEncountersConflictInFragments()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              ...A
              ...B
            }
            fragment A on Type {
              x: a
            }
            fragment B on Type {
              x: b
            }
            '),
            [fieldConflict('x', 'a and b are different fields', [[6, 3], [9, 3]])]
        );
    }

    public function testReportsEachConlictOnce()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              f1 {
                ...A
                ...B
              }
              f2 {
                ...B
                ...A
              }
              f3 {
                ...A
                ...B
                x: c
              }
            }
            fragment A on Type {
              x: a
            }
            fragment B on Type {
              x: b
            }
            '),
            [
                fieldConflict('x', 'a and b are different fields', [[17, 3], [20, 3]]),
                fieldConflict('x', 'c and a are different fields', [[13, 5], [17, 3]]),
                fieldConflict('x', 'c and b are different fields', [[13, 5], [20, 3]]),
            ]
        );
    }

    public function testDeepConflict()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field {
                x: a
              },
              field {
                x: b
              }
            }
            '),
            [
                fieldConflict(
                    'field',
                    [['x', 'a and b are different fields']],
                    [[2, 3], [3, 5], [5, 3], [6, 5]]
                )
            ]
        );
    }

    public function testDeepConflictWithMultipleIssues()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field {
                x: a
                y: c
              },
              field {
                x: b
                y: d
              }
            }
            '),
            [
                fieldConflict(
                    'field',
                    [
                        ['x', 'a and b are different fields'],
                        ['y', 'c and d are different fields'],
                    ],
                    [[2, 3], [3, 5], [4, 5], [6, 3], [7, 5], [8, 5]]
                )
            ]
        );
    }

    public function testVeryDeepConflict()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field {
                deepField {
                  x: a
                }
              },
              field {
                deepField {
                  x: b
                }
              }
            }
            '),
            [
                fieldConflict(
                    'field',
                    ['deepField', [['x', 'a and b are different fields']]],
                    [[2, 3], [3, 5], [4, 7], [7, 3], [8, 5], [9, 7]]
                )
            ]
        );
    }

    public function testReportsDeepConflictToNearestCommonAncestor()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field {
                deepField {
                  x: a
                }
                deepField {
                  x: b
                }
              },
              field {
                deepField {
                  y
                }
              }
            }
            '),
            [
                fieldConflict(
                    'deepField',
                    ['x', 'a and b are different fields'],
                    [[3, 5], [4, 7], [6, 5], [7, 7]]
                )
            ]
        );
    }

    public function testReportsDeepConflictToNearestCommonAncestorInFragments()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field {
                ...F
              }
              field {
                ...F
              }
            }
            fragment F on T {
              deepField {
                deeperField {
                  x: a
                }
                deeperField {
                  x: b
                }
              },
              deepField {
                deeperField {
                  y
                }
              }
            }
            '),
            [
                fieldConflict(
                    'deeperField',
                    ['x', 'a and b are different fields'],
                    [[11, 5], [12, 7], [14, 5], [15, 7]]
                )
            ]
        );
    }

    public function testReportsDeepConflictsInNestedFragments()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field {
                ...F
              }
              field {
                ...I
              }
            }
            fragment F on T {
              x: a
              ...G
            }
            fragment G on T {
              y: c
            }
            fragment I on T {
              y: d
              ...J
            }
            fragment J on T {
              x: b
            }
            '),
            [
                fieldConflict(
                    'field',
                    [
                        ['x', 'a and b are different fields'],
                        ['y', 'c and d are different fields'],
                    ],
                    [[2, 3], [10, 3], [14, 3], [5, 3], [21, 3], [17, 3]]
                )
            ]
        );
    }

    public function testIgnoresUnknownFragments()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field
              ...Unknown
              ...Known
            }
            fragment Known on T {
              field
              ...OtherUnknown
            }
            ')
        );
    }

    public function testConflictingReturnTypesWhichPotentiallyOverlap()
    {
        // This is invalid since an object could potentially be both the Object
        // type IntBox and the interface type NonNullStringBox1. While that
        // condition does not exist in the current schema, the schema could
        // expand in the future to allow this. Thus it is invalid.
        $this->expectFailsRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ...on IntBox {
                  scalar
                }
                ...on NonNullStringBox1 {
                  scalar
                }
              }
            }
            '),
            [
                fieldConflict(
                    'scalar',
                    'they return conflicting types Int and String!',
                    [[4, 7], [7, 7]]
                )
            ]
        );
    }

    public function testCompatibleReturnShapesOnDifferentReturnTypes()
    {
        // In this case `deepBox` returns `SomeBox` in the first usage, and
        // `StringBox` in the second usage. These return types are not the same!
        // however this is valid because the return *shapes* are compatible.
        $this->expectPassesRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ... on SomeBox {
                  deepBox {
                    unrelatedField
                  }
                }
                ... on StringBox {
                  deepBox {
                    unrelatedField
                  }
                }
              }
            }
            ')
        );
    }

    public function testDisallowsDifferingReturnTypesDespiteNoOverlap()
    {
        $this->expectFailsRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ... on IntBox {
                  scalar
                }
                ... on StringBox {
                  scalar
                }
              }
            }
            '),
            [
                fieldConflict(
                    'scalar',
                    'they return conflicting types Int and String',
                    [[4, 7], [7, 7]]
                )
            ]
        );
    }

    public function testReportsCorrectlyWhenANonExclusiveFollowsAnExclusive()
    {
        $this->expectFailsRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ... on IntBox {
                  deepBox {
                    ...X
                  }
                }
              }
              someBox {
                ... on StringBox {
                  deepBox {
                    ...Y
                  }
                }
              }
              memoed: someBox {
                ... on IntBox {
                  deepBox {
                    ...X
                  }
                }
              }
              memoed: someBox {
                ... on StringBox {
                  deepBox {
                    ...Y
                  }
                }
              }
              other: someBox {
                ...X
              }
              other: someBox {
                ...Y
              }
            }
            fragment X on SomeBox {
              scalar
            }
            fragment Y on SomeBox {
              scalar: unrelatedField
            }
            '),
            [
                fieldConflict(
                    'other',
                    ['scalar', 'scalar and unrelatedField are different fields'],
                    [[30, 3], [38, 3], [33, 3], [41, 3]]
                )
            ]
        );
    }

    public function testDisallowsDifferingReturnTypeNullabilityDespiteNoOverlap()
    {
        $this->expectFailsRuleWithSchema(
            $this->schema,
            $this->rule,
            '
{
  someBox {
    ... on NonNullStringBox1 {
      scalar
    }
    ... on StringBox {
      scalar
    }
  }
}
',
            [
                fieldConflict(
                    'scalar',
                    'they return conflicting types String! and String',
                    [[5, 7], [8, 7]]
                )
            ]
        );
    }

    public function testDisallowsDifferingReturnTypeListDespiteNoOverlap()
    {
        $this->expectFailsRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ... on IntBox {
                  box: listStringBox {
                    scalar
                  }
                }
                ... on StringBox {
                  box: stringBox {
                    scalar
                  }
                }
              }
            }
            '),
            [
                fieldConflict(
                    'box',
                    'they return conflicting types [StringBox] and StringBox',
                    [[4, 7], [9, 7]]
                )
            ]
        );

        $this->expectFailsRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ... on IntBox {
                  box: stringBox {
                    scalar
                  }
                }
                ... on StringBox {
                  box: listStringBox {
                    scalar
                  }
                }
              }
            }
            '),
            [
                fieldConflict(
                    'box',
                    'they return conflicting types StringBox and [StringBox]',
                    [[4, 7], [9, 7]]
                )
            ]
        );
    }

    public function testDisallowsDifferingSubfields()
    {
        $this->expectFailsRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ... on IntBox {
                  box: stringBox {
                    val: scalar
                    val: unrelatedField
                  }
                }
                ... on StringBox {
                  box: stringBox {
                    val: scalar
                  }
                }
              }
            }
            '),
            [
                fieldConflict(
                    'val',
                    'scalar and unrelatedField are different fields',
                    [[5, 9], [6, 9]]
                )
            ]
        );
    }

    public function testDisallowsDifferingDeepReturnTypesDespiteNoOverlap()
    {
        $this->expectFailsRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ... on IntBox {
                  box: stringBox {
                    scalar
                  }
                }
                ... on StringBox {
                  box: intBox {
                    scalar
                  }
                }
              }
            }
            '),
            [
                fieldConflict(
                    'box',
                    ['scalar', 'they return conflicting types String and Int'],
                    [[4, 7], [5, 9], [9, 7], [10, 9]]
                )
            ]
        );
    }

    public function testAllowsNonConflictingOverlappingTypes()
    {
        $this->expectPassesRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ... on IntBox {
                  scalar: unrelatedField
                }
                ... on StringBox {
                  scalar
                }
              }
            }
            ')
        );
    }

    public function testSameWrappedScalarReturnTypes()
    {
        $this->expectPassesRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ...on NonNullStringBox1 {
                  scalar
                }
                ...on NonNullStringBox2 {
                  scalar
                }
              }
            }
            ')
        );
    }

    public function testAllowsInlineTypelessFragments()
    {
        $this->expectPassesRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              a
              ... {
                a
              }
            }
            ')
        );
    }

    public function testComparesDeepTypesIncludingList()
    {
        $this->expectFailsRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              connection {
                ...edgeID
                edges {
                  node {
                    id: name
                  }
                }
              }
            }
            
            fragment edgeID on Connection {
              edges {
                node {
                  id
                }
              }
            }
            '),
            [
                fieldConflict(
                    'edges',
                    ['node', [['id', 'name and id are different fields']]],
                    [[4, 5], [5, 7], [6, 9], [13, 3], [14, 5], [15, 7]]
                )
            ]
        );
    }

    public function testIgnoresUnknownTypes()
    {
        $this->expectPassesRuleWithSchema(
            $this->schema,
            $this->rule,
            dedent('
            {
              someBox {
                ...on UnknownType {
                  scalar
                }
                ...on NonNullStringBox2 {
                  scalar
                }
              }
            }
            ')
        );
    }

    public function testDoesNotInfiniteLoopOnRecursiveFragment()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment fragA on Human { name, relatives { name, ...fragA } }
            ')
        );
    }

    public function testDoesNotInfiniteLoopOnImmediatelyRecursiveFragments()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment fragA on Human { name, ...fragA }
            ')
        );
    }

    public function testDoesNotInfiniteLoopOnTransitivelyRecursiveFragments()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment fragA on Human { name, ...fragB }
            fragment fragB on Human { name, ...fragC }
            fragment fragC on Human { name, ...fragA }
            ')
        );
    }

    public function testFindsInvalidCaseEvenWithImmediatelyRecursiveFragment()
    {
        $this->markTestIncomplete('BUG: Finds three conflicts, but should only find one.');

        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment sameAliasesWithDifferentFieldTargets on Dog {
              ...sameAliasesWithDifferentFieldTargets
              fido: name
              fido: nickname
            }
            '),
            [
                fieldConflict(
                    'fido',
                    'name and nickname are different fields',
                    [[3, 3], [4, 3]]
                )
            ]
        );
    }
}
