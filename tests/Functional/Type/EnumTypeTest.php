<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Schema\Schema;
use function Digia\GraphQL\Type\booleanType;
use function Digia\GraphQL\Type\newEnumType;
use function Digia\GraphQL\Type\intType;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;

class EnumTypeTest extends TestCase
{

    /**
     * @var EnumType
     */
    protected $colorType;

    /**
     * @var EnumType
     */
    protected $complex1;

    /**
     * @var EnumType
     */
    protected $complex2;

    /**
     * @var EnumType
     */
    protected $complexEnum;

    /**
     * @var ObjectType
     */
    protected $queryType;

    /**
     * @var ObjectType
     */
    protected $mutationType;

    /**
     * @var ObjectType
     */
    protected $subscriptionType;

    /**
     * @var Schema
     */
    protected $schema;

    public function setUp()
    {
        $this->colorType = newEnumType([
            'name'   => 'Color',
            'values' => [
                'RED'   => ['value' => 0],
                'GREEN' => ['value' => 1],
                'BLUE'  => ['value' => 2],
            ]
        ]);

        $this->complex1 = [
            'someRandomFunction' => function () {
            },
        ];

        $this->complex2 = [
            'someRandomValue' => 123,
        ];

        $this->complexEnum = newEnumType([
            'name'   => 'Complex',
            'values' => [
                'ONE' => ['value' => $this->complex1],
                'TWO' => ['value' => $this->complex2],
            ],
        ]);

        $this->queryType = newObjectType([
            'name'   => 'Query',
            'fields' => [
                'colorEnum'   => [
                    'type'    => $this->colorType,
                    'args'    => [
                        'fromEnum'   => ['type' => $this->colorType],
                        'fromInt'    => ['type' => intType()],
                        'fromString' => ['type' => stringType()],
                    ],
                    'resolve' => function ($value, $args) {
                        return $args['fromInt'] ?? $args['fromString'] ?? $args['fromEnum'];
                    },
                ],
                'colorInt'    => [
                    'type'    => intType(),
                    'args'    => [
                        'fromEnum' => ['type' => $this->colorType],
                        'fromInt'  => ['type' => intType()],
                    ],
                    'resolve' => function ($value, $args) {
                        return $args['fromInt'] ?? $args['fromEnum'];
                    },
                ],
                'complexEnum' => [
                    'type'    => $this->complexEnum,
                    'args'    => [
                        'fromEnum'          => [
                            'type'         => $this->complexEnum,
                            'defaultValue' => $this->complex1,
                        ],
                        'providedGoodValue' => ['type' => booleanType()],
                        'providedBadValue'  => ['type' => booleanType()],
                    ],
                    'resolve' => function ($value, $args) {
                        if ($args['providedGoodValue']) {
                            return $this->complex2;
                        }
                        if ($args['providedBadValue']) {
                            return ['someRandomValue' => 123];
                        }
                        return $args['fromEnum'];
                    }
                ],
            ],
        ]);

        $this->mutationType = newObjectType([
            'name'   => 'Mutation',
            'fields' => [
                'favoriteEnum' => [
                    'type'    => $this->colorType,
                    'args'    => ['color' => ['type' => $this->colorType]],
                    'resolve' => function ($value, $args) {
                        return $args['color'];
                    },
                ],
            ],
        ]);

        $this->subscriptionType = newObjectType([
            'name'   => 'Subscription',
            'fields' => [
                'subscribeToEnum' => [
                    'type'    => $this->colorType,
                    'args'    => ['color' => ['type' => $this->colorType]],
                    'resolve' => function ($value, $args) {
                        return $args['color'];
                    },
                ],
            ],
        ]);

        $this->schema = newSchema([
            'query'        => $this->queryType,
            'mutation'     => $this->mutationType,
            'subscription' => $this->subscriptionType,
        ]);
    }

    // TODO: Add missing tests when possible.

    public function testPresentsAGetValuesAPIForComplexEnums()
    {
        $values = $this->complexEnum->getValues();

        $this->assertEquals(2, count($values));
        $this->assertEquals('ONE', $values[0]->getName());
        $this->assertEquals($this->complex1, $values[0]->getValue());
        $this->assertEquals('TWO', $values[1]->getName());
        $this->assertEquals($this->complex2, $values[1]->getValue());
    }

    public function testPresentsAGetValueAPIForComplexEnums()
    {
        $oneValue = $this->complexEnum->getValue('ONE');

        $this->assertEquals('ONE', $oneValue->getName());
        $this->assertEquals($this->complex1, $oneValue->getValue());
    }

    // TODO: Add test for 'may be internally represented with complex values'.

    // TODO: Add test for 'can be introspected without error'.
}
