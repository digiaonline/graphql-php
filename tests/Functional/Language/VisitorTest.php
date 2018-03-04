<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Config\ConfigTrait;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\FieldsTrait;
use Digia\GraphQL\Language\AST\Node\NameNode;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
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
            function (NodeInterface $node, ?string $key, array $path = []) use (&$visited): ?NodeInterface {
                $visited[] = ['enter', array_slice($path, 0)];
                return $node;
            },
            function (NodeInterface $node, ?string $key, array $path = []) use (&$visited): ?NodeInterface {
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
            function (NodeInterface $node, ?string $key, array $path = []): ?NodeInterface {
                if ($node instanceof OperationDefinitionNode) {
                    return $node->setConfigValue('didEnter', true);
                }
                return $node;
            },
            function (NodeInterface $node, ?string $key, array $path = []): ?NodeInterface {
                if ($node instanceof OperationDefinitionNode) {
                    return $node->setConfigValue('didLeave', true);
                }
                return $node;
            }
        );

        /** @var DocumentNode $editedAst */
        $editedAst = $ast->accept($visitor);

        /** @var ConfigObject $editedNode */
        $editedNode = $editedAst->getDefinitions()[0];

        $this->assertTrue($editedNode->getConfigValue('didEnter'));
        $this->assertTrue($editedNode->getConfigValue('didLeave'));
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
            function (NodeInterface $node, ?string $key, array $path = []): ?NodeInterface {
                if ($node instanceof DocumentNode) {
                    return $node->setConfigValue('didEnter', true);
                }
                return $node;
            },
            function (NodeInterface $node, ?string $key, array $path = []): ?NodeInterface {
                if ($node instanceof DocumentNode) {
                    return $node->setConfigValue('didLeave', true);
                }
                return $node;
            }
        );

        /** @var ConfigTrait $editedAst */
        $editedAst = $ast->accept($visitor);

        $this->assertTrue($editedAst->getConfigValue('didEnter'));
        $this->assertTrue($editedAst->getConfigValue('didLeave'));
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
            function (NodeInterface $node, ?string $key, array $path = []): ?NodeInterface {
                if ($node instanceof FieldNode && $node->getNameValue() === 'b') {
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
            $editedAst->toArray()
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
            function (NodeInterface $node, ?string $key, array $path = []): ?NodeInterface {
                if ($node instanceof FieldNode && $node->getNameValue() === 'b') {
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
            $editedAst->toArray()
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testVisitsEditedNode()
    {
        $addedField = (new FieldNode([
            'name' => new NameNode([
                'value' => '__typename',
            ]),
        ]))->setConfigValue('isAddedField', true);

        $didVisitEditedNode = false;

        /** @var DocumentNode $ast */
        $ast = parse('{ a { x } }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (NodeInterface $node, ?string $key, array $path = []) use (&$didVisitEditedNode, $addedField): ?NodeInterface {
                if ($node instanceof FieldNode && $node->getNameValue() === 'a') {
                    return $addedField;
                }

                if ($node->getConfigValue('isAddedField')) {
                    $didVisitEditedNode = true;
                }

                return $node;
            }
        );

        $ast->accept($visitor);

        $this->assertTrue($didVisitEditedNode);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testAllowsSkippingSubTree()
    {
        $visited = [];

        /** @var DocumentNode $ast */
        $ast = parse('{ a, b { x }, c }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (NodeInterface $node, ?string $key, array $path = []) use (&$visited): ?NodeInterface {
                $visited[] = ['enter', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                if ($node instanceof FieldNode && $node->getNameValue() === 'b') {
                    return null;
                }

                return $node;
            },
            function (NodeInterface $node, ?string $key, array $path = []) use (&$visited): ?NodeInterface {
                $visited[] = ['leave', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                return $node;
            }
        );

        $ast->accept($visitor);

        $this->assertEquals([
            ['enter', 'Document', null],
            ['enter', 'OperationDefinition', null],
            ['enter', 'SelectionSet', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'a'],
            ['leave', 'Name', 'a'],
            ['leave', 'Field', null],
            ['enter', 'Field', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'c'],
            ['leave', 'Name', 'c'],
            ['leave', 'Field', null],
            ['leave', 'SelectionSet', null],
            ['leave', 'OperationDefinition', null],
            ['leave', 'Document', null],
        ], $visited);
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
     */
    public function enterNode(NodeInterface $node, ?string $key = null, array $path = []): ?NodeInterface
    {
        return null !== $this->enterFunction
            ? call_user_func($this->enterFunction, $node, $key, $path)
            : $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node, ?string $key = null, array $path = []): ?NodeInterface
    {
        return null !== $this->leaveFunction
            ? call_user_func($this->leaveFunction, $node, $key, $path)
            : $node;
    }
}
