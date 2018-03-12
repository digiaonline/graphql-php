<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\OverlappingFieldsCanBeMergedRule;
use function Digia\GraphQL\Test\Functional\Validation\fieldConflict;
use function Digia\GraphQL\Type\GraphQLID;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

function SomeBox()
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'   => 'SomeBox',
            'fields' => function () {
                return [
                    'deepBox'        => ['type' => SomeBox()],
                    'unrelatedField' => ['type' => GraphQLString()],
                ];
            },
        ]);
}

function StringBox()
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'   => 'StringBox',
            'fields' => function () {
                return [
                    'scalar'         => ['type' => GraphQLString()],
                    'deepBox'        => ['type' => StringBox()],
                    'unrelatedField' => ['type' => GraphQLString()],
                    'listStringBox'  => ['type' => GraphQLList(StringBox())],
                    'stringBox'      => ['type' => StringBox()],
                    'intBox'         => ['type' => IntBox()],
                ];
            },
        ]);
}

function IntBox()
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'   => 'IntBox',
            'fields' => function () {
                return [
                    'scalar'         => ['type' => GraphQLInt()],
                    'deepBox'        => ['type' => IntBox()],
                    'unrelatedField' => ['type' => GraphQLString()],
                    'listStringBox'  => ['type' => GraphQLList(StringBox())],
                    'stringBox'      => ['type' => StringBox()],
                    'intBox'         => ['type' => IntBox()],
                ];
            },
        ]);
}

function NonNullStringBox1()
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLInterfaceType([
            'name'   => 'NonNullStringBox1',
            'fields' => function () {
                return [
                    'scalar' => ['type' => GraphQLNonNull(GraphQLString())],
                ];
            },
        ]);
}

function NonNullStringBox1Impl()
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'       => 'NonNullStringBox1Impl',
            'interfaces' => [SomeBox(), NonNullStringBox1()],
            'fields'     => function () {
                return [
                    'scalar'         => ['type' => GraphQLNonNull(GraphQLString())],
                    'unrelatedField' => ['type' => GraphQLString()],
                    'deepBox'        => ['type' => SomeBox()],
                ];
            },
        ]);
}

function NonNullStringBox2()
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLInterfaceType([
            'name'   => 'NonNullStringBox2',
            'fields' => function () {
                return [
                    'scalar' => ['type' => GraphQLNonNull(GraphQLString())],
                ];
            },
        ]);
}

function NonNullStringBox2Impl()
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'       => 'NonNullStringBox2Impl',
            'interfaces' => [SomeBox(), NonNullStringBox2()],
            'fields'     => function () {
                return [
                    'scalar'         => ['type' => GraphQLNonNull(GraphQLString())],
                    'unrelatedField' => ['type' => GraphQLString()],
                    'deepBox'        => ['type' => SomeBox()],
                ];
            },
        ]);
}

function Connection()
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
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
}

function schema()
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'QueryRoot',
                'fields' => function () {
                    return [
                        'someBox'    => ['type' => SomeBox()],
                        'connection' => ['type' => Connection()],
                    ];
                },
            ]),
            'types' => [IntBox(), StringBox(), NonNullStringBox1Impl(), NonNullStringBox2Impl()],
        ]);
}

class OverlappingFieldsCanBeMergedRuleTest extends RuleTestCase
{
    public function testUniqueFields()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment uniqueFields on Dog {
  name
  nickname
}
'
        );
    }

    public function testIdenticalFields()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment mergeIdenticalFields on Dog {
  name
  name
}
'
        );
    }

    public function testIdenticalFieldsWithIdenticalArguments()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment mergeIdenticalFieldsWithIdenticalArgs on Dog {
  doesKnowCommand(dogCommand: SIT)
  doesKnowCommand(dogCommand: SIT)
}
'
        );
    }

    public function testIdenticalFieldsWithIdenticalDirectives()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment mergeSameFieldsWithSameDirectives on Dog {
  name @include(if: true)
  name @include(if: true)
}
'
        );
    }

    public function testDifferentArgumentsWithDifferentAliases()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment differentArgsWithDifferentAliases on Dog {
  knowsSit: doesKnowCommand(dogCommand: SIT)
  knowsDown: doesKnowCommand(dogCommand: DOWN)
}
'
        );
    }

    public function testDifferentDirectivesWithDifferentAliases()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment differentDirectivesWithDifferentAliases on Dog {
  nameIfTrue: name @include(if: true)
  nameIfFalse: name @include(if: false)
}
'
        );
    }

    public function testDifferentSkipIncludeDirectivesAccepted()
    {
        // Note: Differing skip/include directives don't create an ambiguous return
        // value and are acceptable in conditions where differing runtime values
        // may have the same desired effect of including or skipping a field.
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment differentDirectivesWithDifferentAliases on Dog {
  name @include(if: true)
  name @include(if: false)
}
'
        );
    }

    public function testSameAliasesWithDifferentFieldTargets()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment sameAliasesWithDifferentFieldTargets on Dog {
  fido: name
  fido: nickname
}
',
            [fieldConflict('fido', 'name and nickname are different fields', [[3, 3], [4, 3]])]
        );
    }

    public function testSameAliasesAllowedOnNonOverlappingFields()
    {
        // This is valid since no object can be both a "Dog" and a "Cat", thus
        // these fields can never overlap.
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment sameAliasesWithDifferentFieldTargets on Pet {
  ... on Dog {
    name
  }
  ... on Cat {
    name: nickname
  }
}
'
        );
    }

    public function testAliasMaskingDirectFieldAccess()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment aliasMaskingDirectFieldAccess on Dog {
  name: nickname
  name
}
',
            [fieldConflict('name', 'nickname and name are different fields', [[3, 3], [4, 3]])]
        );
    }

    public function testDifferentArgumentsSecondAddsAnArgument()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment conflictingArgs on Dog {
  doesKnowCommand
  doesKnowCommand(dogCommand: HEEL)
}
',
            [fieldConflict('doesKnowCommand', 'they have differing arguments', [[3, 3], [4, 3]])]
        );
    }

    public function testDifferentArgsSecondMissingAnArgument()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment conflictingArgs on Dog {
  doesKnowCommand(dogCommand: SIT)
  doesKnowCommand
}
',
            [fieldConflict('doesKnowCommand', 'they have differing arguments', [[3, 3], [4, 3]])]
        );
    }

    public function testConflictingArguments()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment conflictingArgs on Dog {
  doesKnowCommand(dogCommand: SIT)
  doesKnowCommand(dogCommand: HEEL)
}
',
            [fieldConflict('doesKnowCommand', 'they have differing arguments', [[3, 3], [4, 3]])]
        );
    }

    public function testAllowsDifferentArgumentsWhereNoConflictIsPossible()
    {
        // This is valid since no object can be both a "Dog" and a "Cat", thus
        // these fields can never overlap.
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment conflictingArgs on Pet {
  ... on Dog {
    name(surname: true)
  }
  ... on Cat {
    name
  }
}
'
        );
    }

    public function testEncountersConflictInFragments()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [fieldConflict('x', 'a and b are different fields', [[7, 3], [10, 3]])]
        );
    }

    public function testReportsEachConlictOnce()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict('x', 'a and b are different fields', [[18, 3], [21, 3]]),
                fieldConflict('x', 'c and a are different fields', [[14, 5], [18, 3]]),
                fieldConflict('x', 'c and b are different fields', [[14, 5], [21, 3]]),
            ]
        );
    }

    public function testDeepConflict()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
{
  field {
    x: a
  },
  field {
    x: b
  }
}
',
            [
                fieldConflict(
                    'field',
                    [['x', 'a and b are different fields']],
                    [[3, 3], [4, 5], [6, 3], [7, 5]]
                )
            ]
        );
    }

    public function testDeepConflictWithMultipleIssues()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'field',
                    [
                        ['x', 'a and b are different fields'],
                        ['y', 'c and d are different fields'],
                    ],
                    [
                        [3, 3],
                        [4, 5],
                        [5, 5],
                        [7, 3],
                        [8, 5],
                        [9, 5],
                    ]
                )
            ]
        );
    }

    public function testVeryDeepConflict()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'field',
                    ['deepField', [['x', 'a and b are different fields']]],
                    [
                        [3, 3],
                        [4, 5],
                        [5, 7],
                        [8, 3],
                        [9, 5],
                        [10, 7],
                    ]
                )
            ]
        );
    }

    public function testReportsDeepConflictToNearestCommonAncestor()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'deepField',
                    ['x', 'a and b are different fields'],
                    [
                        [4, 5],
                        [5, 7],
                        [7, 5],
                        [8, 7],
                    ]
                )
            ]
        );
    }

    public function testReportsDeepConflictToNearestCommonAncestorInFragments()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'deeperField',
                    ['x', 'a and b are different fields'],
                    [
                        [12, 5],
                        [13, 7],
                        [15, 5],
                        [16, 7],
                    ]
                )
            ]
        );
    }

    public function testReportsDeepConflictsInNestedFragments()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'field',
                    [
                        ['x', 'a and b are different fields'],
                        ['y', 'c and d are different fields'],
                    ],
                    [
                        [3, 3],
                        [11, 3],
                        [15, 3],
                        [6, 3],
                        [22, 3],
                        [18, 3],
                    ]
                )
            ]
        );
    }

    public function testIgnoresUnknownFragments()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
{
  field
  ...Unknown
  ...Known
}
fragment Known on T {
  field
  ...OtherUnknown
}
'
        );
    }

    public function testConflictingReturnTypesWhichPotentiallyOverlap()
    {
        // This is invalid since an object could potentially be both the Object
        // type IntBox and the interface type NonNullStringBox1. While that
        // condition does not exist in the current schema, the schema could
        // expand in the future to allow this. Thus it is invalid.
        $this->expectFailsRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'scalar',
                    'they return conflicting types Int and String!',
                    [[5, 7], [8, 7]]
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
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
'
        );
    }

    public function testDisallowsDifferingReturnTypesDespiteNoOverlap()
    {
        $this->expectFailsRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'scalar',
                    'they return conflicting types Int and String',
                    [[5, 7], [8, 7]]
                )
            ]
        );
    }

    public function testReportsCorrectlyWhenANonExclusiveFollowsAnExclusive()
    {
        $this->expectFailsRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'other',
                    ['scalar', 'scalar and unrelatedField are different fields'],
                    [[31, 3], [39, 3], [34, 3], [42, 3]]
                )
            ]
        );
    }

    public function testDisallowsDifferingReturnTypeNullabilityDespiteNoOverlap()
    {
        $this->expectFailsRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
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
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'box',
                    'they return conflicting types [StringBox] and StringBox',
                    [[5, 7], [10, 7]]
                )
            ]
        );

        $this->expectFailsRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'box',
                    'they return conflicting types StringBox and [StringBox]',
                    [[5, 7], [10, 7]]
                )
            ]
        );
    }

    public function testDisallowsDifferingSubfields()
    {
        $this->expectFailsRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'val',
                    'scalar and unrelatedField are different fields',
                    [[6, 9], [7, 9]]
                )
            ]
        );
    }

    public function testDisallowsDifferingDeepReturnTypesDespiteNoOverlap()
    {
        $this->expectFailsRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'box',
                    ['scalar', 'they return conflicting types String and Int'],
                    [[5, 7], [6, 9], [10, 7], [11, 9]]
                )
            ]
        );
    }

    public function testAllowsNonConflictingOverlappingTypes()
    {
        $this->expectPassesRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
'
        );
    }

    public function testSameWrappedScalarReturnTypes()
    {
        $this->expectPassesRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
'
        );
    }

    public function testAllowsInlineTypelessFragments()
    {
        $this->expectPassesRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
{
  a
  ... {
    a
  }
}
'
        );
    }

    public function testComparesDeepTypesIncludingList()
    {
        $this->expectFailsRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
',
            [
                fieldConflict(
                    'edges',
                    ['node', [['id', 'name and id are different fields']]],
                    [
                        [5, 5],
                        [6, 7],
                        [7, 9],
                        [14, 3],
                        [15, 5],
                        [16, 7],
                    ]
                )
            ]
        );
    }

    public function testIgnoresUnknownTypes()
    {
        $this->expectPassesRuleWithSchema(
            schema(),
            new OverlappingFieldsCanBeMergedRule(),
            '
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
'
        );
    }

    public function testDoesNotInfiniteLoopOnRecursiveFragment()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment fragA on Human { name, relatives { name, ...fragA } }
'
        );
    }

    public function testDoesNotInfiniteLoopOnImmediatelyRecursiveFragments()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment fragA on Human { name, ...fragA }
'
        );
    }

    public function testDoesNotInfiniteLoopOnTransitivelyRecursiveFragments()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment fragA on Human { name, ...fragB }
fragment fragB on Human { name, ...fragC }
fragment fragC on Human { name, ...fragA }
'
        );
    }

    public function testFindsInvalidCaseEvenWithImmediatelyRecursiveFragment()
    {
        $this->markTestIncomplete('BUG: Finds three conflicts, but should only find one.');

        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment sameAliasesWithDifferentFieldTargets on Dog {
  ...sameAliasesWithDifferentFieldTargets
  fido: name
  fido: nickname
}
',
            [
                fieldConflict(
                    'fido',
                    'name and nickname are different fields',
                    [[4, 3], [5, 3]]
                )
            ]
        );
    }
}
