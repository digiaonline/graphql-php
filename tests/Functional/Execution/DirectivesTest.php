<?php

namespace Digia\GraphQL\Test\Functional\Execution;


use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Schema\Schema;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class DirectivesTest extends TestCase
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var array
     */
    private $data = [
        'a' => 'a',
        'b' => 'b'
    ];

    public function setUp()
    {
        parent::setUp();

        $this->schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'TestType',
                'fields' => [
                    'a' => ['type' => GraphQLString()],
                    'b' => ['type' => GraphQLString()]
                ]
            ])
        ]);
    }

    // WORKS WITHOUT DIRECTIVES

    /**
     * works without directives
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testWorksWithDirective()
    {
        $result = execute($this->schema, parse('{ a, b }'), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }

    /**
     * if true includes scalar
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIfTrueReturnScalar()
    {
        $result = execute($this->schema, parse('{ a, b @include(if: true) }'), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }

    /**
     * if false omits on scalar
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIfFalseOmitScalar()
    {
        $result = execute($this->schema, parse('{ a, b @include(if: false) }'), $this->data);

        $this->assertEquals(['a' => 'a'], $result->getData());
    }

    /**
     * unless false includes scalar
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUnlessFalseIncludesScalar()
    {
        $result = execute($this->schema, parse('{ a, b @skip(if: false) }'), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }

    /**
     * unless true omits scalar
     * 
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUnlessTrueOmitScalar()
    {
        $result = execute($this->schema, parse('{ a, b @skip(if: true) }'), $this->data);

        $this->assertEquals(['a' => 'a'], $result->getData());
    }

    // WORKS ON FRAGMENT SPREADS

    /**
     * if false omits fragment spread
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIfFalseOmitsFragmentSpread()
    {
        $source = 'query {
          a
          ...Frag @include(if: false)
        }
        fragment Frag on TestType {
          b
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals(['a' => 'a'], $result->getData());
    }


    /**
     * if true includes fragment spread
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIfTrueIncludesFragmentSpread()
    {
        $source = 'query {
          a
          ...Frag @include(if: true)
        }
        fragment Frag on TestType {
          b
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }

    /**
     * unless false includes fragment spread
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUnlessFalseIncludesFragmentSpread()
    {
        $source = 'query {
          a
          ...Frag @skip(if: false)
        }
        fragment Frag on TestType {
          b
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }

    /**
     * unless true omits fragment spread
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUnlessTrueOmitsFragmentSpread()
    {
        $source = 'query {
          a
          ...Frag @skip(if: true)
        }
        fragment Frag on TestType {
          b
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals(['a' => 'a'], $result->getData());
    }

    // WORKS ON INLINE FRAGMENT

    /**
     * if false omits inline fragment
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIfFalseOmitsInlineFragment()
    {
        $source = 'query {
          a
          ... on TestType @include(if: false) {
            b
          }
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals(['a' => 'a'], $result->getData());
    }

    /**
     * if true includes inline fragment
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIfTrueIncludesInlineFragment()
    {
        $source = 'query {
          a
          ... on TestType @include(if: true) {
            b
          }
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }

    /**
     * unless false includes inline fragment
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUnlessFalseIncludesInlineFragment()
    {
        $source = 'query {
          a
          ... on TestType @skip(if: false) {
            b
          }
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }

    /**
     * unless true includes inline fragment
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUnlessTrueIncludesInlineFragments()
    {
        $source = 'query {
          a
          ... on TestType @skip(if: true) {
            b
          }
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals(['a' => 'a'], $result->getData());
    }

    // WORKS ON ANONYMOUS INLINE FRAGMENT

    /**
     * if false omits anonymous inline fragment
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIfFalseOmitsAnonymousInlineFragment()
    {
        $source = 'query {
          a
          ... @include(if: false) {
            b
          }
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals(['a' => 'a'], $result->getData());
    }

    /**
     * if true includes anonymous inline fragment
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIfTrueIncludeAnonymousInlineFragment()
    {
        $source = 'query {
          a
          ... @include(if: true) {
            b
          }
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }

    /**
     * unless false includes anonymous inline fragment
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUnlessFalseIncludesAnonymousInlineFragment()
    {
        $source = 'query Q {
          a
          ... @skip(if: false) {
            b
          }
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }

    /**
     * unless true includes anonymous inline fragment
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUnlessTrueIncludeAnonymousInlineFragment()
    {
        $source = 'query {
          a
          ... @skip(if: true) {
            b
          }
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals(['a' => 'a'], $result->getData());
    }

    // WORKS WITH SKIP AND INCLUDE DIRECTIVES

    /**
     * include and no skip
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIncludeAndNoSkip()
    {
        $source = '{
          a
          b @include(if: true) @skip(if: false)
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }

    /**
     * include and skip
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIncludeAndSkip()
    {
        $source = '{
          a
          b @include(if: true) @skip(if: true)
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals(['a' => 'a'], $result->getData());
    }

    /**
     * no include or skip
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testNoIncludeOrSkip()
    {
        $source = '{
          a
          b @include(if: false) @skip(if: false)
        }';

        $result = execute($this->schema, parse($source), $this->data);

        $this->assertEquals(['a' => 'a'], $result->getData());
    }
}
