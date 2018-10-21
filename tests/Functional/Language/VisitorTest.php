<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Language\NodeBuilderInterface;
use Digia\GraphQL\Language\Visitor\ParallelVisitor;
use Digia\GraphQL\Language\Visitor\TypeInfoVisitor;
use Digia\GraphQL\Language\Visitor\Visitor;
use Digia\GraphQL\Language\Visitor\VisitorBreak;
use Digia\GraphQL\Language\Visitor\VisitorInfo;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use Digia\GraphQL\Util\TypeInfo;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Test\Functional\Validation\testSchema;
use function Digia\GraphQL\Test\readFileContents;
use function Digia\GraphQL\Type\getNamedType;

class VisitorTest extends TestCase
{
    public function testValidatesPathArgument()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a }');

        $visitor = new Visitor(
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $visited[] = ['enter', array_slice($node->getVisitorInfo()->getPath(), 0)];

                return new VisitorResult($node);
            },
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $visited[] = ['leave', array_slice($node->getVisitorInfo()->getPath(), 0)];

                return new VisitorResult($node);
            }
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast->acceptVisitor(new VisitorInfo($visitor));

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

    public function testAllowsForEditingOnEnter()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $document = parse('{ a, b, c { a, b, c } }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (NodeInterface $node): VisitorResult {
                if ($node instanceof FieldNode && $node->getNameValue() === 'b') {
                    return new VisitorResult(null, VisitorResult::ACTION_REPLACE);
                }

                return new VisitorResult($node);
            }
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $editedDocument = $document->acceptVisitor(new VisitorInfo($visitor));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            parse('{ a, b, c { a, b, c } }', ['noLocation' => true])->toArray(),
            $document->toAST()
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            parse('{ a,    c { a,    c } }', ['noLocation' => true])->toArray(),
            $editedDocument->toAST()
        );
    }

    public function testAllowsForEditingOnLeave()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $document = parse('{ a, b, c { a, b, c } }', ['noLocation' => true]);

        $visitor = new Visitor(
            null,
            function (NodeInterface $node): VisitorResult {
                if ($node instanceof FieldNode && $node->getNameValue() === 'b') {
                    return new VisitorResult(null, VisitorResult::ACTION_REPLACE);
                }

                return new VisitorResult($node);
            }
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $editedDocument = $document->acceptVisitor(new VisitorInfo($visitor));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            parse('{ a, b, c { a, b, c } }', ['noLocation' => true])->toArray(),
            $document->toAST()
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            parse('{ a,    c { a,    c } }', ['noLocation' => true])->toArray(),
            $editedDocument->toAST()
        );
    }

    public function testVisitsEditedNode()
    {
        $addedField = new AddedFieldNode(
            null,
            new NameNode('__typename', null),
            [],
            [],
            null,
            null
        );

        $didVisitEditedNode = false;

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a { x } }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (NodeInterface $node) use (&$didVisitEditedNode, $addedField): VisitorResult {
                if ($node instanceof FieldNode && $node->getNameValue() === 'a') {
                    return new VisitorResult($addedField);
                }

                if ($node instanceof AddedFieldNode) {
                    $didVisitEditedNode = true;
                }

                return new VisitorResult($node);
            }
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast->acceptVisitor(new VisitorInfo($visitor));

        $this->assertTrue($didVisitEditedNode);
    }

    public function testAllowsSkippingSubTree()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a, b { x }, c }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $visited[] = ['enter', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                if ($node instanceof FieldNode && $node->getNameValue() === 'b') {
                    return new VisitorResult(null);
                }

                return new VisitorResult($node);
            },
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $visited[] = ['leave', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                return new VisitorResult($node);
            }
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast->acceptVisitor(new VisitorInfo($visitor));

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

    public function testAllowsEarlyExitWhileVisiting()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a, b { x }, c }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $visited[] = ['enter', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                if ($node instanceof NameNode && $node->getValue() === 'x') {
                    return new VisitorResult($node, VisitorResult::ACTION_BREAK);
                }

                return new VisitorResult($node);
            },
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $visited[] = ['leave', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                return new VisitorResult($node);
            }
        );

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        $this->assertEquals([
            ['enter', 'Document', null],
            ['enter', 'OperationDefinition', null],
            ['enter', 'SelectionSet', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'a'],
            ['leave', 'Name', 'a'],
            ['leave', 'Field', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'b'],
            ['leave', 'Name', 'b'],
            ['enter', 'SelectionSet', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'x'],
        ], $visited);
    }

    public function testAllowsEarlyExitWhileLeaving()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a, b { x }, c }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $visited[] = ['enter', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                return new VisitorResult($node);
            },
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $visited[] = ['leave', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                if ($node instanceof NameNode && $node->getValue() === 'x') {
                    return new VisitorResult($node, VisitorResult::ACTION_BREAK);
                }

                return new VisitorResult($node);
            }
        );

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        $this->assertEquals([
            ['enter', 'Document', null],
            ['enter', 'OperationDefinition', null],
            ['enter', 'SelectionSet', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'a'],
            ['leave', 'Name', 'a'],
            ['leave', 'Field', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'b'],
            ['leave', 'Name', 'b'],
            ['enter', 'SelectionSet', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'x'],
            ['leave', 'Name', 'x'],
        ], $visited);
    }

    public function testAllowsAKindVisitor()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a, b { x }, c }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (NodeInterface $node) use (&$visited): VisitorResult {
                if ($node instanceof NameNode || $node instanceof SelectionSetNode) {
                    $visited[] = ['enter', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];
                }

                return new VisitorResult($node);
            },
            function (NodeInterface $node) use (&$visited): VisitorResult {
                if ($node instanceof SelectionSetNode) {
                    $visited[] = ['leave', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];
                }

                return new VisitorResult($node);
            }
        );

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        $this->assertEquals([
            ['enter', 'SelectionSet', null],
            ['enter', 'Name', 'a'],
            ['enter', 'Name', 'b'],
            ['enter', 'SelectionSet', null],
            ['enter', 'Name', 'x'],
            ['leave', 'SelectionSet', null],
            ['enter', 'Name', 'c'],
            ['leave', 'SelectionSet', null],
        ], $visited);
    }

    public function testVisitsVariablesDefinedInFragments()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('fragment a($v: Boolean = false) on t { f }', ['noLocation' => true]);

        $visitor = new Visitor(
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $visited[] = ['enter', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                return new VisitorResult($node);
            },
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $visited[] = ['leave', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                return new VisitorResult($node);
            }
        );

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        $this->assertEquals([
            ['enter', 'Document', null],
            ['enter', 'FragmentDefinition', null],
            ['enter', 'Name', 'a'],
            ['leave', 'Name', 'a'],
            ['enter', 'VariableDefinition', null],
            ['enter', 'Variable', null],
            ['enter', 'Name', 'v'],
            ['leave', 'Name', 'v'],
            ['leave', 'Variable', null],
            ['enter', 'NamedType', null],
            ['enter', 'Name', 'Boolean'],
            ['leave', 'Name', 'Boolean'],
            ['leave', 'NamedType', null],
            ['enter', 'BooleanValue', false],
            ['leave', 'BooleanValue', false],
            ['leave', 'VariableDefinition', null],
            ['enter', 'NamedType', null],
            ['enter', 'Name', 't'],
            ['leave', 'Name', 't'],
            ['leave', 'NamedType', null],
            ['enter', 'SelectionSet', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'f'],
            ['leave', 'Name', 'f'],
            ['leave', 'Field', null],
            ['leave', 'SelectionSet', null],
            ['leave', 'FragmentDefinition', null],
            ['leave', 'Document', null],
        ], $visited);
    }

    public function testVisitsKitchenSink()
    {
        $visited = [];

        $kitchenSink = readFileContents(__DIR__ . '/kitchen-sink.graphql');

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse($kitchenSink);

        $visitor = new Visitor(
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $key       = $node->getVisitorInfo()->getKey();
                $parent    = $node->getVisitorInfo()->getParent();
                $visited[] = ['enter', $node->getKind(), $key, $parent ? $parent->getKind() : null];

                return new VisitorResult($node);
            },
            function (NodeInterface $node) use (&$visited): VisitorResult {
                $key       = $node->getVisitorInfo()->getKey();
                $parent    = $node->getVisitorInfo()->getParent();
                $visited[] = ['leave', $node->getKind(), $key, $parent ? $parent->getKind() : null];

                return new VisitorResult($node);
            }
        );

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        $this->assertEquals([
            ['enter', 'Document', null, null],
            ['enter', 'OperationDefinition', 0, null],
            ['enter', 'Name', 'name', 'OperationDefinition'],
            ['leave', 'Name', 'name', 'OperationDefinition'],
            ['enter', 'VariableDefinition', 0, null],
            ['enter', 'Variable', 'variable', 'VariableDefinition'],
            ['enter', 'Name', 'name', 'Variable'],
            ['leave', 'Name', 'name', 'Variable'],
            ['leave', 'Variable', 'variable', 'VariableDefinition'],
            ['enter', 'NamedType', 'type', 'VariableDefinition'],
            ['enter', 'Name', 'name', 'NamedType'],
            ['leave', 'Name', 'name', 'NamedType'],
            ['leave', 'NamedType', 'type', 'VariableDefinition'],
            ['leave', 'VariableDefinition', 0, null],
            ['enter', 'VariableDefinition', 1, null],
            ['enter', 'Variable', 'variable', 'VariableDefinition'],
            ['enter', 'Name', 'name', 'Variable'],
            ['leave', 'Name', 'name', 'Variable'],
            ['leave', 'Variable', 'variable', 'VariableDefinition'],
            ['enter', 'NamedType', 'type', 'VariableDefinition'],
            ['enter', 'Name', 'name', 'NamedType'],
            ['leave', 'Name', 'name', 'NamedType'],
            ['leave', 'NamedType', 'type', 'VariableDefinition'],
            ['enter', 'EnumValue', 'defaultValue', 'VariableDefinition'],
            ['leave', 'EnumValue', 'defaultValue', 'VariableDefinition'],
            ['leave', 'VariableDefinition', 1, null],
            ['enter', 'SelectionSet', 'selectionSet', 'OperationDefinition'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'alias', 'Field'],
            ['leave', 'Name', 'alias', 'Field'],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'Argument', 0, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'ListValue', 'value', 'Argument'],
            ['enter', 'IntValue', 0, null],
            ['leave', 'IntValue', 0, null],
            ['enter', 'IntValue', 1, null],
            ['leave', 'IntValue', 1, null],
            ['leave', 'ListValue', 'value', 'Argument'],
            ['leave', 'Argument', 0, null],
            ['enter', 'SelectionSet', 'selectionSet', 'Field'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['leave', 'Field', 0, null],
            ['enter', 'InlineFragment', 1, null],
            ['enter', 'NamedType', 'typeCondition', 'InlineFragment'],
            ['enter', 'Name', 'name', 'NamedType'],
            ['leave', 'Name', 'name', 'NamedType'],
            ['leave', 'NamedType', 'typeCondition', 'InlineFragment'],
            ['enter', 'Directive', 0, null],
            ['enter', 'Name', 'name', 'Directive'],
            ['leave', 'Name', 'name', 'Directive'],
            ['leave', 'Directive', 0, null],
            ['enter', 'SelectionSet', 'selectionSet', 'InlineFragment'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'SelectionSet', 'selectionSet', 'Field'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['leave', 'Field', 0, null],
            ['enter', 'Field', 1, null],
            ['enter', 'Name', 'alias', 'Field'],
            ['leave', 'Name', 'alias', 'Field'],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'Argument', 0, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'IntValue', 'value', 'Argument'],
            ['leave', 'IntValue', 'value', 'Argument'],
            ['leave', 'Argument', 0, null],
            ['enter', 'Argument', 1, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'Variable', 'value', 'Argument'],
            ['enter', 'Name', 'name', 'Variable'],
            ['leave', 'Name', 'name', 'Variable'],
            ['leave', 'Variable', 'value', 'Argument'],
            ['leave', 'Argument', 1, null],
            ['enter', 'Directive', 0, null],
            ['enter', 'Name', 'name', 'Directive'],
            ['leave', 'Name', 'name', 'Directive'],
            ['enter', 'Argument', 0, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'Variable', 'value', 'Argument'],
            ['enter', 'Name', 'name', 'Variable'],
            ['leave', 'Name', 'name', 'Variable'],
            ['leave', 'Variable', 'value', 'Argument'],
            ['leave', 'Argument', 0, null],
            ['leave', 'Directive', 0, null],
            ['enter', 'SelectionSet', 'selectionSet', 'Field'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['leave', 'Field', 0, null],
            ['enter', 'FragmentSpread', 1, null],
            ['enter', 'Name', 'name', 'FragmentSpread'],
            ['leave', 'Name', 'name', 'FragmentSpread'],
            ['leave', 'FragmentSpread', 1, null],
            ['leave', 'SelectionSet', 'selectionSet', 'Field'],
            ['leave', 'Field', 1, null],
            ['leave', 'SelectionSet', 'selectionSet', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'InlineFragment'],
            ['leave', 'InlineFragment', 1, null],
            ['enter', 'InlineFragment', 2, null],
            ['enter', 'Directive', 0, null],
            ['enter', 'Name', 'name', 'Directive'],
            ['leave', 'Name', 'name', 'Directive'],
            ['enter', 'Argument', 0, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'Variable', 'value', 'Argument'],
            ['enter', 'Name', 'name', 'Variable'],
            ['leave', 'Name', 'name', 'Variable'],
            ['leave', 'Variable', 'value', 'Argument'],
            ['leave', 'Argument', 0, null],
            ['leave', 'Directive', 0, null],
            ['enter', 'SelectionSet', 'selectionSet', 'InlineFragment'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'InlineFragment'],
            ['leave', 'InlineFragment', 2, null],
            ['enter', 'InlineFragment', 3, null],
            ['enter', 'SelectionSet', 'selectionSet', 'InlineFragment'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'InlineFragment'],
            ['leave', 'InlineFragment', 3, null],
            ['leave', 'SelectionSet', 'selectionSet', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'OperationDefinition'],
            ['leave', 'OperationDefinition', 0, null],
            ['enter', 'OperationDefinition', 1, null],
            ['enter', 'Name', 'name', 'OperationDefinition'],
            ['leave', 'Name', 'name', 'OperationDefinition'],
            ['enter', 'SelectionSet', 'selectionSet', 'OperationDefinition'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'Argument', 0, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'IntValue', 'value', 'Argument'],
            ['leave', 'IntValue', 'value', 'Argument'],
            ['leave', 'Argument', 0, null],
            ['enter', 'Directive', 0, null],
            ['enter', 'Name', 'name', 'Directive'],
            ['leave', 'Name', 'name', 'Directive'],
            ['leave', 'Directive', 0, null],
            ['enter', 'SelectionSet', 'selectionSet', 'Field'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'SelectionSet', 'selectionSet', 'Field'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'OperationDefinition'],
            ['leave', 'OperationDefinition', 1, null],
            ['enter', 'OperationDefinition', 2, null],
            ['enter', 'Name', 'name', 'OperationDefinition'],
            ['leave', 'Name', 'name', 'OperationDefinition'],
            ['enter', 'VariableDefinition', 0, null],
            ['enter', 'Variable', 'variable', 'VariableDefinition'],
            ['enter', 'Name', 'name', 'Variable'],
            ['leave', 'Name', 'name', 'Variable'],
            ['leave', 'Variable', 'variable', 'VariableDefinition'],
            ['enter', 'NamedType', 'type', 'VariableDefinition'],
            ['enter', 'Name', 'name', 'NamedType'],
            ['leave', 'Name', 'name', 'NamedType'],
            ['leave', 'NamedType', 'type', 'VariableDefinition'],
            ['leave', 'VariableDefinition', 0, null],
            ['enter', 'SelectionSet', 'selectionSet', 'OperationDefinition'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'Argument', 0, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'Variable', 'value', 'Argument'],
            ['enter', 'Name', 'name', 'Variable'],
            ['leave', 'Name', 'name', 'Variable'],
            ['leave', 'Variable', 'value', 'Argument'],
            ['leave', 'Argument', 0, null],
            ['enter', 'SelectionSet', 'selectionSet', 'Field'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'SelectionSet', 'selectionSet', 'Field'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'SelectionSet', 'selectionSet', 'Field'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'Field'],
            ['leave', 'Field', 0, null],
            ['enter', 'Field', 1, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'SelectionSet', 'selectionSet', 'Field'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'Field'],
            ['leave', 'Field', 1, null],
            ['leave', 'SelectionSet', 'selectionSet', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'Field'],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'OperationDefinition'],
            ['leave', 'OperationDefinition', 2, null],
            ['enter', 'FragmentDefinition', 3, null],
            ['enter', 'Name', 'name', 'FragmentDefinition'],
            ['leave', 'Name', 'name', 'FragmentDefinition'],
            ['enter', 'NamedType', 'typeCondition', 'FragmentDefinition'],
            ['enter', 'Name', 'name', 'NamedType'],
            ['leave', 'Name', 'name', 'NamedType'],
            ['leave', 'NamedType', 'typeCondition', 'FragmentDefinition'],
            ['enter', 'SelectionSet', 'selectionSet', 'FragmentDefinition'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'Argument', 0, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'Variable', 'value', 'Argument'],
            ['enter', 'Name', 'name', 'Variable'],
            ['leave', 'Name', 'name', 'Variable'],
            ['leave', 'Variable', 'value', 'Argument'],
            ['leave', 'Argument', 0, null],
            ['enter', 'Argument', 1, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'Variable', 'value', 'Argument'],
            ['enter', 'Name', 'name', 'Variable'],
            ['leave', 'Name', 'name', 'Variable'],
            ['leave', 'Variable', 'value', 'Argument'],
            ['leave', 'Argument', 1, null],
            ['enter', 'Argument', 2, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'ObjectValue', 'value', 'Argument'],
            ['enter', 'ObjectField', 0, null],
            ['enter', 'Name', 'name', 'ObjectField'],
            ['leave', 'Name', 'name', 'ObjectField'],
            ['enter', 'StringValue', 'value', 'ObjectField'],
            ['leave', 'StringValue', 'value', 'ObjectField'],
            ['leave', 'ObjectField', 0, null],
            ['enter', 'ObjectField', 1, null],
            ['enter', 'Name', 'name', 'ObjectField'],
            ['leave', 'Name', 'name', 'ObjectField'],
            ['enter', 'StringValue', 'value', 'ObjectField'],
            ['leave', 'StringValue', 'value', 'ObjectField'],
            ['leave', 'ObjectField', 1, null],
            ['leave', 'ObjectValue', 'value', 'Argument'],
            ['leave', 'Argument', 2, null],
            ['leave', 'Field', 0, null],
            ['leave', 'SelectionSet', 'selectionSet', 'FragmentDefinition'],
            ['leave', 'FragmentDefinition', 3, null],
            ['enter', 'OperationDefinition', 4, null],
            ['enter', 'SelectionSet', 'selectionSet', 'OperationDefinition'],
            ['enter', 'Field', 0, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['enter', 'Argument', 0, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'BooleanValue', 'value', 'Argument'],
            ['leave', 'BooleanValue', 'value', 'Argument'],
            ['leave', 'Argument', 0, null],
            ['enter', 'Argument', 1, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'BooleanValue', 'value', 'Argument'],
            ['leave', 'BooleanValue', 'value', 'Argument'],
            ['leave', 'Argument', 1, null],
            ['enter', 'Argument', 2, null],
            ['enter', 'Name', 'name', 'Argument'],
            ['leave', 'Name', 'name', 'Argument'],
            ['enter', 'NullValue', 'value', 'Argument'],
            ['leave', 'NullValue', 'value', 'Argument'],
            ['leave', 'Argument', 2, null],
            ['leave', 'Field', 0, null],
            ['enter', 'Field', 1, null],
            ['enter', 'Name', 'name', 'Field'],
            ['leave', 'Name', 'name', 'Field'],
            ['leave', 'Field', 1, null],
            ['leave', 'SelectionSet', 'selectionSet', 'OperationDefinition'],
            ['leave', 'OperationDefinition', 4, null],
            ['leave', 'Document', null, null],
        ], $visited);
    }

    public function testVisitInParallel()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a, b { x }, c }');

        $visitor = new ParallelVisitor([
            new Visitor(
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = ['enter', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                    if ($node instanceof FieldNode && $node->getNameValue() === 'b') {
                        return new VisitorResult(null);
                    }

                    return new VisitorResult($node);
                },
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = ['leave', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                    return new VisitorResult($node);
                }
            ),
        ]);

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

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

    public function testAllowsSkippingSubTreeWhenVisitingInParallel()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a { x }, b { y } }', ['noLocation' => true]);

        $visitor = new ParallelVisitor([
            new Visitor(
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = [
                        'no-a',
                        'enter',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null
                    ];

                    if ($node instanceof FieldNode && $node->getNameValue() === 'a') {
                        return new VisitorResult(null);
                    }

                    return new VisitorResult($node);
                },
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = [
                        'no-a',
                        'leave',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null
                    ];

                    return new VisitorResult($node);
                }
            ),
            new Visitor(
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = [
                        'no-b',
                        'enter',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null
                    ];

                    if ($node instanceof FieldNode && $node->getNameValue() === 'b') {
                        return new VisitorResult(null);
                    }

                    return new VisitorResult($node);
                },
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = [
                        'no-b',
                        'leave',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null
                    ];

                    return new VisitorResult($node);
                }
            ),
        ]);

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        $this->assertEquals([
            ['no-a', 'enter', 'Document', null],
            ['no-b', 'enter', 'Document', null],
            ['no-a', 'enter', 'OperationDefinition', null],
            ['no-b', 'enter', 'OperationDefinition', null],
            ['no-a', 'enter', 'SelectionSet', null],
            ['no-b', 'enter', 'SelectionSet', null],
            ['no-a', 'enter', 'Field', null],
            ['no-b', 'enter', 'Field', null],
            ['no-b', 'enter', 'Name', 'a'],
            ['no-b', 'leave', 'Name', 'a'],
            ['no-b', 'enter', 'SelectionSet', null],
            ['no-b', 'enter', 'Field', null],
            ['no-b', 'enter', 'Name', 'x'],
            ['no-b', 'leave', 'Name', 'x'],
            ['no-b', 'leave', 'Field', null],
            ['no-b', 'leave', 'SelectionSet', null],
            ['no-b', 'leave', 'Field', null],
            ['no-a', 'enter', 'Field', null],
            ['no-b', 'enter', 'Field', null],
            ['no-a', 'enter', 'Name', 'b'],
            ['no-a', 'leave', 'Name', 'b'],
            ['no-a', 'enter', 'SelectionSet', null],
            ['no-a', 'enter', 'Field', null],
            ['no-a', 'enter', 'Name', 'y'],
            ['no-a', 'leave', 'Name', 'y'],
            ['no-a', 'leave', 'Field', null],
            ['no-a', 'leave', 'SelectionSet', null],
            ['no-a', 'leave', 'Field', null],
            ['no-a', 'leave', 'SelectionSet', null],
            ['no-b', 'leave', 'SelectionSet', null],
            ['no-a', 'leave', 'OperationDefinition', null],
            ['no-b', 'leave', 'OperationDefinition', null],
            ['no-a', 'leave', 'Document', null],
            ['no-b', 'leave', 'Document', null],
        ], $visited);
    }

    public function testAllowsEarlyExitWhileEnteringWhenVisitingInParallel()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a, b { x }, c }', ['noLocation' => true]);

        $visitor = new ParallelVisitor([
            new Visitor(
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = ['enter', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                    if ($node instanceof NameNode && $node->getValue() === 'x') {
                        return new VisitorResult($node, VisitorResult::ACTION_BREAK);
                    }

                    return new VisitorResult($node);
                },
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = ['leave', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                    return new VisitorResult($node);
                }
            ),
        ]);

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        $this->assertEquals([
            ['enter', 'Document', null],
            ['enter', 'OperationDefinition', null],
            ['enter', 'SelectionSet', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'a'],
            ['leave', 'Name', 'a'],
            ['leave', 'Field', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'b'],
            ['leave', 'Name', 'b'],
            ['enter', 'SelectionSet', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'x'],
        ], $visited);
    }

    public function testAllowsEarlyExitWhileLeavingWhenVisitingInParallel()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a, b { x }, c }', ['noLocation' => true]);

        $visitor = new ParallelVisitor([
            new Visitor(
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = ['enter', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                    return new VisitorResult($node);
                },
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = ['leave', $node->getKind(), $node instanceof NameNode ? $node->getValue() : null];

                    if ($node instanceof NameNode && $node->getValue() === 'x') {
                        return new VisitorResult($node, VisitorResult::ACTION_BREAK);
                    }

                    return new VisitorResult($node);
                }
            ),
        ]);

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        $this->assertEquals([
            ['enter', 'Document', null],
            ['enter', 'OperationDefinition', null],
            ['enter', 'SelectionSet', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'a'],
            ['leave', 'Name', 'a'],
            ['leave', 'Field', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'b'],
            ['leave', 'Name', 'b'],
            ['enter', 'SelectionSet', null],
            ['enter', 'Field', null],
            ['enter', 'Name', 'x'],
            ['leave', 'Name', 'x'],
        ], $visited);
    }

    public function testAllowsEarlyExitFromDifferentPointsWhenVisitingInParallel()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ a { y }, b { x } }', ['noLocation' => true]);

        $visitor = new ParallelVisitor([
            new Visitor(
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = [
                        'break-a',
                        'enter',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null
                    ];

                    if ($node instanceof NameNode && $node->getValue() === 'a') {
                        return new VisitorResult($node, VisitorResult::ACTION_BREAK);
                    }

                    return new VisitorResult($node);
                },
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = [
                        'break-a',
                        'leave',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null
                    ];

                    return new VisitorResult($node);
                }
            ),
            new Visitor(
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = [
                        'break-b',
                        'enter',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null
                    ];

                    if ($node instanceof NameNode && $node->getValue() === 'b') {
                        return new VisitorResult($node, VisitorResult::ACTION_BREAK);
                    }

                    return new VisitorResult($node);
                },
                function (NodeInterface $node) use (&$visited): VisitorResult {
                    $visited[] = [
                        'break-b',
                        'leave',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null
                    ];

                    return new VisitorResult($node);
                }
            ),
        ]);

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        $this->assertEquals([
            ['break-a', 'enter', 'Document', null],
            ['break-b', 'enter', 'Document', null],
            ['break-a', 'enter', 'OperationDefinition', null],
            ['break-b', 'enter', 'OperationDefinition', null],
            ['break-a', 'enter', 'SelectionSet', null],
            ['break-b', 'enter', 'SelectionSet', null],
            ['break-a', 'enter', 'Field', null],
            ['break-b', 'enter', 'Field', null],
            ['break-a', 'enter', 'Name', 'a'],
            ['break-b', 'enter', 'Name', 'a'],
            ['break-b', 'leave', 'Name', 'a'],
            ['break-b', 'enter', 'SelectionSet', null],
            ['break-b', 'enter', 'Field', null],
            ['break-b', 'enter', 'Name', 'y'],
            ['break-b', 'leave', 'Name', 'y'],
            ['break-b', 'leave', 'Field', null],
            ['break-b', 'leave', 'SelectionSet', null],
            ['break-b', 'leave', 'Field', null],
            ['break-b', 'enter', 'Field', null],
            ['break-b', 'enter', 'Name', 'b'],
        ], $visited);
    }

    public function testMaintainsTypeInfoDuringVisit()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ human(id: 4) { name, pets { ... { name } }, unknown } }');

        $typeInfo = new TypeInfo(testSchema());
        $visitor  = new TypeInfoVisitor(
            $typeInfo,
            new Visitor(
                function (NodeInterface $node) use (&$visited, $typeInfo): VisitorResult {
                    $parentType = $typeInfo->getParentType();
                    $type       = $typeInfo->getType();
                    $inputType  = $typeInfo->getInputType();
                    $visited[]  = [
                        'enter',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null,
                        $parentType ? (string)$parentType : null,
                        $type ? (string)$type : null,
                        $inputType ? (string)$inputType : null,
                    ];

                    return new VisitorResult($node);
                },
                function (NodeInterface $node) use (&$visited, $typeInfo): VisitorResult {
                    $parentType = $typeInfo->getParentType();
                    $type       = $typeInfo->getType();
                    $inputType  = $typeInfo->getInputType();
                    $visited[]  = [
                        'leave',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null,
                        $parentType ? (string)$parentType : null,
                        $type ? (string)$type : null,
                        $inputType ? (string)$inputType : null,
                    ];

                    return new VisitorResult($node);
                }
            )
        );

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        $this->assertEquals([
            ['enter', 'Document', null, null, null, null],
            ['enter', 'OperationDefinition', null, null, 'QueryRoot', null],
            ['enter', 'SelectionSet', null, 'QueryRoot', 'QueryRoot', null],
            ['enter', 'Field', null, 'QueryRoot', 'Human', null],
            ['enter', 'Name', 'human', 'QueryRoot', 'Human', null],
            ['leave', 'Name', 'human', 'QueryRoot', 'Human', null],
            ['enter', 'Argument', null, 'QueryRoot', 'Human', 'ID'],
            ['enter', 'Name', 'id', 'QueryRoot', 'Human', 'ID'],
            ['leave', 'Name', 'id', 'QueryRoot', 'Human', 'ID'],
            ['enter', 'IntValue', null, 'QueryRoot', 'Human', 'ID'],
            ['leave', 'IntValue', null, 'QueryRoot', 'Human', 'ID'],
            ['leave', 'Argument', null, 'QueryRoot', 'Human', 'ID'],
            ['enter', 'SelectionSet', null, 'Human', 'Human', null],
            ['enter', 'Field', null, 'Human', 'String', null],
            ['enter', 'Name', 'name', 'Human', 'String', null],
            ['leave', 'Name', 'name', 'Human', 'String', null],
            ['leave', 'Field', null, 'Human', 'String', null],
            ['enter', 'Field', null, 'Human', '[Pet]', null],
            ['enter', 'Name', 'pets', 'Human', '[Pet]', null],
            ['leave', 'Name', 'pets', 'Human', '[Pet]', null],
            ['enter', 'SelectionSet', null, 'Pet', '[Pet]', null],
            ['enter', 'InlineFragment', null, 'Pet', 'Pet', null],
            ['enter', 'SelectionSet', null, 'Pet', 'Pet', null],
            ['enter', 'Field', null, 'Pet', 'String', null],
            ['enter', 'Name', 'name', 'Pet', 'String', null],
            ['leave', 'Name', 'name', 'Pet', 'String', null],
            ['leave', 'Field', null, 'Pet', 'String', null],
            ['leave', 'SelectionSet', null, 'Pet', 'Pet', null],
            ['leave', 'InlineFragment', null, 'Pet', 'Pet', null],
            ['leave', 'SelectionSet', null, 'Pet', '[Pet]', null],
            ['leave', 'Field', null, 'Human', '[Pet]', null],
            ['enter', 'Field', null, 'Human', null, null],
            ['enter', 'Name', 'unknown', 'Human', null, null],
            ['leave', 'Name', 'unknown', 'Human', null, null],
            ['leave', 'Field', null, 'Human', null, null],
            ['leave', 'SelectionSet', null, 'Human', 'Human', null],
            ['leave', 'Field', null, 'QueryRoot', 'Human', null],
            ['leave', 'SelectionSet', null, 'QueryRoot', 'QueryRoot', null],
            ['leave', 'OperationDefinition', null, null, 'QueryRoot', null],
            ['leave', 'Document', null, null, null, null],
        ], $visited);
    }

    public function testMaintainsTypeInfoDuringEdit()
    {
        $visited = [];

        /** @noinspection PhpUnhandledExceptionInspection */
        $ast = parse('{ human(id: 4) { name, pets }, alien }');

        $nodeBuilder = GraphQL::make(NodeBuilderInterface::class);

        $typeInfo = new TypeInfo(testSchema());
        $visitor  = new TypeInfoVisitor(
            $typeInfo,
            new Visitor(
                function (NodeInterface $node) use (&$visited, $typeInfo, $nodeBuilder): VisitorResult {
                    $parentType = $typeInfo->getParentType();
                    $type       = $typeInfo->getType();
                    $inputType  = $typeInfo->getInputType();

                    $visited[] = [
                        'enter',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null,
                        $parentType ? (string)$parentType : null,
                        $type ? (string)$type : null,
                        $inputType ? (string)$inputType : null,
                    ];

                    if ($node instanceof FieldNode
                        && null === $node->getSelectionSet()
                        && getNamedType($type) instanceof CompositeTypeInterface
                    ) {
                        return new VisitorResult($nodeBuilder->build([
                            'kind'         => NodeKindEnum::FIELD,
                            'alias'        => $node->getAliasAST(),
                            'name'         => $node->getNameAST(),
                            'arguments'    => $node->getArgumentsAST(),
                            'directives'   => $node->getDirectivesAST(),
                            'selectionSet' => [
                                'kind'   => NodeKindEnum::SELECTION_SET,
                                'fields' => [
                                    [
                                        'kind' => NodeKindEnum::FIELD,
                                        'name' => ['value' => '__typename'],
                                    ]
                                ]
                            ],
                        ]), VisitorResult::ACTION_REPLACE);
                    }

                    return new VisitorResult($node);
                },
                function (NodeInterface $node) use (&$visited, $typeInfo): VisitorResult {
                    $parentType = $typeInfo->getParentType();
                    $type       = $typeInfo->getType();
                    $inputType  = $typeInfo->getInputType();

                    $visited[] = [
                        'leave',
                        $node->getKind(),
                        $node instanceof NameNode ? $node->getValue() : null,
                        $parentType ? (string)$parentType : null,
                        $type ? (string)$type : null,
                        $inputType ? (string)$inputType : null,
                    ];

                    return new VisitorResult($node);
                }
            )
        );

        try {
            $ast->acceptVisitor(new VisitorInfo($visitor));
        } catch (VisitorBreak $e) {
        }

        // TODO: Add asserts for print once the printer is implemented

        $this->markTestIncomplete('BUG: Nodes added by visitors are visited either too early or too late.');

        $this->assertEquals([
            ['enter', 'Document', null, null, null, null],
            ['enter', 'OperationDefinition', null, null, 'QueryRoot', null],
            ['enter', 'SelectionSet', null, 'QueryRoot', 'QueryRoot', null],
            ['enter', 'Field', null, 'QueryRoot', 'Human', null],
            ['enter', 'Name', 'human', 'QueryRoot', 'Human', null],
            ['leave', 'Name', 'human', 'QueryRoot', 'Human', null],
            ['enter', 'Argument', null, 'QueryRoot', 'Human', 'ID'],
            ['enter', 'Name', 'id', 'QueryRoot', 'Human', 'ID'],
            ['leave', 'Name', 'id', 'QueryRoot', 'Human', 'ID'],
            ['enter', 'IntValue', null, 'QueryRoot', 'Human', 'ID'],
            ['leave', 'IntValue', null, 'QueryRoot', 'Human', 'ID'],
            ['leave', 'Argument', null, 'QueryRoot', 'Human', 'ID'],
            ['enter', 'SelectionSet', null, 'Human', 'Human', null],
            ['enter', 'Field', null, 'Human', 'String', null],
            ['enter', 'Name', 'name', 'Human', 'String', null],
            ['leave', 'Name', 'name', 'Human', 'String', null],
            ['leave', 'Field', null, 'Human', 'String', null],
            ['enter', 'Field', null, 'Human', '[Pet]', null],
            ['enter', 'Name', 'pets', 'Human', '[Pet]', null],
            ['leave', 'Name', 'pets', 'Human', '[Pet]', null],
            ['enter', 'SelectionSet', null, 'Pet', '[Pet]', null],
            ['enter', 'Field', null, 'Pet', 'String!', null],
            ['enter', 'Name', '__typename', 'Pet', 'String!', null],
            ['leave', 'Name', '__typename', 'Pet', 'String!', null],
            ['leave', 'Field', null, 'Pet', 'String!', null],
            ['leave', 'SelectionSet', null, 'Pet', '[Pet]', null],
            ['leave', 'Field', null, 'Human', '[Pet]', null],
            ['leave', 'SelectionSet', null, 'Human', 'Human', null],
            ['leave', 'Field', null, 'QueryRoot', 'Human', null],
            ['enter', 'Field', null, 'QueryRoot', 'Alien', null],
            ['enter', 'Name', 'alien', 'QueryRoot', 'Alien', null],
            ['leave', 'Name', 'alien', 'QueryRoot', 'Alien', null],
            ['enter', 'SelectionSet', null, 'Alien', 'Alien', null],
            ['enter', 'Field', null, 'Alien', 'String!', null],
            ['enter', 'Name', '__typename', 'Alien', 'String!', null],
            ['leave', 'Name', '__typename', 'Alien', 'String!', null],
            ['leave', 'Field', null, 'Alien', 'String!', null],
            ['leave', 'SelectionSet', null, 'Alien', 'Alien', null],
            ['leave', 'Field', null, 'QueryRoot', 'Alien', null],
            ['leave', 'SelectionSet', null, 'QueryRoot', 'QueryRoot', null],
            ['leave', 'OperationDefinition', null, null, 'QueryRoot', null],
            ['leave', 'Document', null, null, null, null],
        ], $visited);
    }
}

class AddedFieldNode extends FieldNode
{
}
