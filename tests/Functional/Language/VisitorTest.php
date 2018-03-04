<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Visitor\AbstractVisitor;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\parse;

class VisitorTest extends TestCase
{

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testValidatesPathArgument()
    {
        $visited = [];

        /** @var DocumentNode $ast */
        $ast = parse('{ a }');

        $visitor = new Visitor(
            function (array $node, ?string $key, array $path = []) use (&$visited): ?array {
                $visited[] = ['enter', array_slice($path, 0)];
                return $node;
            },
            function (array $node, ?string $key, array $path = []) use (&$visited): ?array {
                $visited[] = ['leave', array_slice($path, 0)];
                return $node;
            }
        );

        $ast->accept($visitor);

        $this->assertEquals([
            ['enter', []],
            ['enter', ['definitions', 0]],
            ['enter', ['definitions', 0, 'selectionSet']],
            ['enter', ['definitions', 0, 'selectionSet', 'selections', 0]],
            ['enter', ['definitions', 0, 'selectionSet', 'selections', 0, 'name']],
            ['leave', ['definitions', 0, 'selectionSet', 'selections', 0, 'name']],
            ['leave', ['definitions', 0, 'selectionSet', 'selections', 0]],
            ['leave', ['definitions', 0, 'selectionSet']],
            ['leave', ['definitions', 0]],
            ['leave', []],
        ], $visited);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testAllowsEditingANodeBothOnEnterAndOnLeave()
    {
        /** @var DocumentNode $ast */
        $ast = parse('{ a, b, c { a, b, c } }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (array $node, ?string $key, array $path = []): ?array {
                if ($node['kind'] === NodeKindEnum::OPERATION_DEFINITION) {
                    return array_merge(['didEnter' => true], $node);
                }
                return $node;
            },
            function (array $node, ?string $key, array $path = []): ?array {
                if ($node['kind'] === NodeKindEnum::OPERATION_DEFINITION) {
                    return array_merge(['didLeave' => true], $node);
                }
                return $node;
            }
        );

        $editedAst = $ast->accept($visitor);

        $this->assertEquals(array_merge($ast->toArray(), [
            'definitions' => [
                0 => array_merge($ast->getDefinitionsAsArray()[0], [
                    'didEnter' => true,
                    'didLeave' => true,
                ]),
            ],
        ]), $editedAst);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testAllowsEditingTheRootNodeOnEnterAndOnLeave()
    {
        /** @var DocumentNode $ast */
        $ast = parse('{ a, b, c { a, b, c } }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (array $node, ?string $key, array $path = []): ?array {
                if ($node['kind'] === NodeKindEnum::DOCUMENT) {
                    return array_merge(['didEnter' => true], $node);
                }
                return $node;
            },
            function (array $node, ?string $key, array $path = []): ?array {
                if ($node['kind'] === NodeKindEnum::DOCUMENT) {
                    return array_merge(['didLeave' => true], $node);
                }
                return $node;
            }
        );

        $editedAst = $ast->accept($visitor);

        $this->assertEquals(array_merge($ast->toArray(), [
            'didEnter' => true,
            'didLeave' => true,
        ]), $editedAst);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testAllowsForEditingOnEnter()
    {
        /** @var DocumentNode $ast */
        $ast = parse('{ a, b, c { a, b, c } }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (array $node, ?string $key, array $path = []): ?array {
                if ($node['kind'] === NodeKindEnum::FIELD && isset($node['name']['value']) && $node['name']['value'] === 'b') {
                    return null;
                }
                return $node;
            }
        );

        $editedAst = $ast->accept($visitor);

        $this->assertEquals(
            parse('{ a, b, c { a, b, c } }', ['noLocation' => true])->toArray(),
            $ast->toArray()
        );

        $this->assertEquals(
            parse('{ a,    c { a,    c } }', ['noLocation' => true])->toArray(),
            $editedAst
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testAllowsForEditingOnLeave()
    {
        /** @var DocumentNode $ast */
        $ast = parse('{ a, b, c { a, b, c } }', ['noLocation' => true]);

        $visitor = new Visitor(
            null,
            function (array $node, ?string $key, array $path = []): ?array {
                if ($node['kind'] === NodeKindEnum::FIELD && isset($node['name']['value']) && $node['name']['value'] === 'b') {
                    return null;
                }
                return $node;
            }
        );

        $editedAst = $ast->accept($visitor);

        $this->assertEquals(
            parse('{ a, b, c { a, b, c } }', ['noLocation' => true])->toArray(),
            $ast->toArray()
        );

        $this->assertEquals(
            parse('{ a,    c { a,    c } }', ['noLocation' => true])->toArray(),
            $editedAst
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
//    public function testVisitsEditedNode()
//    {
//        $addedField = [
//            'kind' => NodeKindEnum::FIELD,
//            'name' => [
//                'kind' => NodeKindEnum::NAME,
//                'value' => '__typename',
//            ],
//        ];
//
//        $didVisitEditedNode = false;
//
//        /** @var DocumentNode $ast */
//        $ast = parse('{ a { x } }', ['noLocation' => true]);
//
//        $visitor = new Visitor(
//            function (array $node, ?string $key, array $path = []) use (&$didVisitEditedNode, $addedField): ?array {
//                if ($node['kind'] === NodeKindEnum::FIELD && isset($node['name']['value']) && $node['name']['value'] === 'a') {
//                    return [
//                        'kind' => NodeKindEnum::FIELD,
//                        'selectionSet' => array_merge($node['selectionSet'], [$addedField]),
//                    ];
//                }
//                if ($node == $addedField) {
//                    $didVisitEditedNode = true;
//                }
//                return $node;
//            }
//        );
//
//        $ast->accept($visitor);
//
//        $this->assertTrue($didVisitEditedNode);
//    }
}

class Visitor extends AbstractVisitor
{

    /**
     * @var callable|null
     */
    protected $enterFunction;

    /**
     * @var callable|null
     */
    protected $leaveFunction;

    /**
     * TestableVisitor constructor.
     * @param callable|null $enterFunction
     * @param callable|null $leaveFunction
     */
    public function __construct(?callable $enterFunction = null, ?callable $leaveFunction = null)
    {
        $this->enterFunction = $enterFunction;
        $this->leaveFunction = $leaveFunction;
    }

    /**
     * @inheritdoc
     */
    public function enterNode(array $node, ?string $key = null, array $path = []): ?array
    {
        return null !== $this->enterFunction
            ? call_user_func($this->enterFunction, $node, $key, $path)
            : $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(array $node, ?string $key = null, array $path = []): ?array
    {
        return null !== $this->leaveFunction
            ? call_user_func($this->leaveFunction, $node, $key, $path)
            : $node;
    }
}
