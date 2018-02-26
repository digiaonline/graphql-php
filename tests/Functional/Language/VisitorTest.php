<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Visitor\AbstractVisitor;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Util\SerializationInterface;
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
            function (NodeInterface $node, ?string $key, array $path = []) use (&$visited): ?array {
                /** @var $node SerializationInterface */
                $visited[] = ['enter', array_slice($path, 0)];
                return $node->toArray();
            },
            function (NodeInterface $node, ?string $key, array $path = []) use (&$visited): ?array {
                /** @var $node SerializationInterface */
                $visited[] = ['leave', array_slice($path, 0)];
                return $node->toArray();
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
            function (NodeInterface $node, ?string $key, array $path = []): ?array {
                if ($node->getKind() === NodeKindEnum::OPERATION_DEFINITION) {
                    return array_merge([
                        'didEnter' => true,
                    ], $node->toArray());
                }
                return $node->toArray();
            },
            function (NodeInterface $node, ?string $key, array $path = []): ?array {
                if ($node->getKind() === NodeKindEnum::OPERATION_DEFINITION) {
                    return array_merge([
                        'didLeave' => true,
                    ], $node->toArray());
                }
                return $node->toArray();
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
            function (NodeInterface $node, ?string $key, array $path = []): ?array {
                if ($node->getKind() === NodeKindEnum::DOCUMENT) {
                    return array_merge([
                        'didEnter' => true,
                    ], $node->toArray());
                }
                return $node->toArray();
            },
            function (NodeInterface $node, ?string $key, array $path = []): ?array {
                if ($node->getKind() === NodeKindEnum::DOCUMENT) {
                    return array_merge([
                        'didLeave' => true,
                    ], $node->toArray());
                }
                return $node->toArray();
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
            function (NodeInterface $node, ?string $key, array $path = []): ?array {
                if ($node->getKind() === NodeKindEnum::FIELD && $node->getNameValue() === 'b') {
                    return null;
                }
                return $node->toArray();
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
     * @param NodeInterface|SerializationInterface $node
     */
    public function enterNode(NodeInterface $node, ?string $key = null, array $path = []): ?array
    {
        return null !== $this->enterFunction
            ? call_user_func($this->enterFunction, $node, $key, $path)
            : $node->toArray();
    }

    /**
     * @inheritdoc
     * @param NodeInterface|SerializationInterface $node
     */
    public function leaveNode(NodeInterface $node, ?string $key = null, array $path = []): ?array
    {
        return null !== $this->leaveFunction
            ? call_user_func($this->leaveFunction, $node, $key, $path)
            : $node->toArray();
    }
}
