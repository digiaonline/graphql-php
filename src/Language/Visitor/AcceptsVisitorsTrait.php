<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\NodeBuilder;
use Digia\GraphQL\Language\NodeBuilderInterface;
use Digia\GraphQL\Util\SerializationInterface;

trait AcceptsVisitorsTrait
{

    /**
     * @var NodeBuilderInterface
     */
    private static $nodeBuilder;

    /**
     * @var VisitorInterface
     */
    protected $visitor;

    /**
     * @var string|int|null
     */
    protected $key;

    /**
     * @var NodeInterface|null
     */
    protected $parent;

    /**
     * @var array
     */
    protected $path;

    /**
     * @var array
     */
    protected $ancestors = [];

    /**
     * @var bool
     */
    protected $isEdited = false;

    /**
     * @param VisitorInterface   $visitor
     * @param mixed              $key
     * @param NodeInterface|null $parent
     * @param string[]           $path
     * @param NodeInterface[]    $ancestors
     * @return NodeInterface|SerializationInterface|null
     */
    public function acceptVisitor(
        VisitorInterface $visitor,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = [],
        array $ancestors = []
    ): ?NodeInterface {
        $this->visitor   = $visitor;
        $this->key       = $key;
        $this->parent    = $parent;
        $this->path      = $path;
        $this->ancestors = $ancestors;

        /** @var NodeInterface $newNode */
        $newNode = clone $this; // TODO: Benchmark cloning

        // If the result was null, it means that we should not traverse this branch.
        if (null === ($newNode = $visitor->enterNode($newNode))) {
            return null;
        }

        // If the node was edited, we want to return early
        // to avoid visiting its sub-tree completely.
        if ($newNode instanceof AcceptsVisitorsInterface && $newNode->determineIsEdited($this)) {
            return $newNode;
        }

        $newAst = $newNode->toAST();

        foreach (self::$kindToNodesToVisitMap[$this->kind] as $propertyName) {
            $nodeOrNodes = $this->{$propertyName};

            if (empty($nodeOrNodes)) {
                continue;
            }

            $propertyAst = $this->visitNodeOrNodes($nodeOrNodes, $propertyName, $newNode);

            if (empty($propertyAst)) {
                continue;
            }

            $newAst[$propertyName] = $propertyAst;
        }

        $newNode = $this->createNode($newAst);

        return $visitor->leaveNode($newNode);
    }

    /**
     * @param array $ast
     * @return NodeInterface
     */
    protected function createNode(array $ast): NodeInterface
    {
        /** @var NodeInterface|AcceptsVisitorsInterface $newNode */
        $newNode = $this->getNodeBuilder()->build($ast);

        $newNode->setVisitor($this->visitor);
        $newNode->setKey($this->key);
        $newNode->setParent($this->parent);
        $newNode->setPath($this->path);
        $newNode->setAncestors($this->ancestors);
        $newNode->setIsEdited($this->isEdited);

        return $newNode;
    }

    /**
     * @inheritdoc
     */
    public function determineIsEdited($node): bool
    {
        $this->isEdited = $this->isEdited || !$this->compareNode($node);
        return $this->isEdited;
    }

    /**
     * @return bool
     */
    public function isEdited(): bool
    {
        return $this->isEdited;
    }

    /**
     * @inheritdoc
     */
    public function getAncestor(int $depth = 1): ?NodeInterface
    {
        if (empty($this->ancestors)) {
            return null;
        }

        $index = \count($this->ancestors) - $depth;

        return $this->ancestors[$index] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getAncestors(): array
    {
        return $this->ancestors;
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function getParent(): ?NodeInterface
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @param VisitorInterface $visitor
     * @return $this
     */
    public function setVisitor(VisitorInterface $visitor)
    {
        $this->visitor = $visitor;
        return $this;
    }

    /**
     * @param mixed $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param NodeInterface|null $parent
     * @return $this
     */
    public function setParent(?NodeInterface $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param array $path
     * @return $this
     */
    public function setPath(array $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param array $ancestors
     * @return $this
     */
    public function setAncestors(array $ancestors)
    {
        $this->ancestors = $ancestors;
        return $this;
    }

    /**
     * @param bool $isEdited
     * @return $this
     */
    public function setIsEdited(bool $isEdited)
    {
        $this->isEdited = $isEdited;
        return $this;
    }

    /**
     * @param NodeInterface|NodeInterface[] $nodeOrNodes
     * @param mixed                         $key
     * @param NodeInterface                 $parent
     * @return array|null
     */
    protected function visitNodeOrNodes($nodeOrNodes, $key, NodeInterface $parent): ?array
    {
        $this->addAncestor($parent);

        $ast = \is_array($nodeOrNodes)
            ? $this->visitNodes($nodeOrNodes, $key)
            : $this->visitNode($nodeOrNodes, $key, $parent);

        $this->removeAncestor();

        return $ast;
    }

    /**
     * @param NodeInterface[]    $nodes
     * @param string|int         $key
     * @param NodeInterface|null $parent
     * @return array
     */
    protected function visitNodes(array $nodes, $key): array
    {
        $this->addOneToPath($key);

        $ast   = [];
        $index = 0;

        foreach ($nodes as $node) {
            $nodeAst = $this->visitNode($node, $index, null);

            if (null !== $nodeAst) {
                $ast[$index] = $nodeAst;
                $index++;
            }
        }

        $this->removeOneFromPath();

        return $ast;
    }

    /**
     * @param NodeInterface|AcceptsVisitorsTrait $node
     * @param string|int                         $key
     * @param NodeInterface|null                 $parent
     * @return array|null
     */
    protected function visitNode($node, $key, ?NodeInterface $parent): ?array
    {
        $this->addOneToPath($key);

        /** @var NodeInterface|AcceptsVisitorsTrait $newNode */
        $newNode = $node->acceptVisitor($this->visitor, $key, $parent, $this->path, $this->ancestors);

        // If the node was edited, we need to revisit it to produce the expected result.
        if (null !== $newNode && $newNode->isEdited()) {
            $newNode = $newNode->acceptVisitor($this->visitor, $key, $parent, $this->path, $this->ancestors);
        }

        $this->removeOneFromPath();

        return null !== $newNode ? $newNode->toAST() : null;
    }

    /**
     * @param NodeInterface $other
     * @return bool
     */
    protected function compareNode(NodeInterface $other)
    {
        // TODO: Figure out a better way to solve this.
        return $this->toJSON() === $other->toJSON();
    }

    /**
     * Appends a key to the path.
     * @param string $key
     */
    protected function addOneToPath(string $key)
    {
        $this->path[] = $key;
    }

    /**
     * Removes the last item from the path.
     */
    protected function removeOneFromPath()
    {
        $this->path = \array_slice($this->path, 0, -1);
    }

    /**
     * Adds an ancestor.
     * @param NodeInterface $node
     */
    protected function addAncestor(NodeInterface $node)
    {
        $this->ancestors[] = $node;
    }

    /**
     *  Removes the last ancestor.
     */
    protected function removeAncestor()
    {
        $this->ancestors = \array_slice($this->ancestors, 0, -1);
    }

    /**
     * @return NodeBuilderInterface
     */
    protected function getNodeBuilder(): NodeBuilderInterface
    {
        if (null === self::$nodeBuilder) {
            self::$nodeBuilder = new NodeBuilder();
        }

        return self::$nodeBuilder;
    }

    /**
     * @var array
     */
    protected static $kindToNodesToVisitMap = [
        'Name' => [],

        'Document'            => ['definitions'],
        'OperationDefinition' => [
            'name',
            'variableDefinitions',
            'directives',
            'selectionSet',
        ],
        'VariableDefinition'  => ['variable', 'type', 'defaultValue'],
        'Variable'            => ['name'],
        'SelectionSet'        => ['selections'],
        'Field'               => ['alias', 'name', 'arguments', 'directives', 'selectionSet'],
        'Argument'            => ['name', 'value'],

        'FragmentSpread'     => ['name', 'directives'],
        'InlineFragment'     => ['typeCondition', 'directives', 'selectionSet'],
        'FragmentDefinition' => [
            'name',
            // Note: fragment variable definitions are experimental and may be changed or removed in the future.
            'variableDefinitions',
            'typeCondition',
            'directives',
            'selectionSet',
        ],

        'IntValue'     => [],
        'FloatValue'   => [],
        'StringValue'  => [],
        'BooleanValue' => [],
        'NullValue'    => [],
        'EnumValue'    => [],
        'ListValue'    => ['values'],
        'ObjectValue'  => ['fields'],
        'ObjectField'  => ['name', 'value'],

        'Directive' => ['name', 'arguments'],

        'NamedType'   => ['name'],
        'ListType'    => ['type'],
        'NonNullType' => ['type'],

        'SchemaDefinition'        => ['directives', 'operationTypes'],
        'OperationTypeDefinition' => ['type'],

        'ScalarTypeDefinition'      => ['description', 'name', 'directives'],
        'ObjectTypeDefinition'      => [
            'description',
            'name',
            'interfaces',
            'directives',
            'fields',
        ],
        'FieldDefinition'           => ['description', 'name', 'arguments', 'type', 'directives'],
        'InputValueDefinition'      => [
            'description',
            'name',
            'type',
            'defaultValue',
            'directives',
        ],
        'InterfaceTypeDefinition'   => ['description', 'name', 'directives', 'fields'],
        'UnionTypeDefinition'       => ['description', 'name', 'directives', 'types'],
        'EnumTypeDefinition'        => ['description', 'name', 'directives', 'values'],
        'EnumValueDefinition'       => ['description', 'name', 'directives'],
        'InputObjectTypeDefinition' => ['description', 'name', 'directives', 'fields'],

        'DirectiveDefinition' => ['description', 'name', 'arguments', 'locations'],

        'SchemaExtension' => ['directives', 'operationTypes'],

        'ScalarTypeExtension'      => ['name', 'directives'],
        'ObjectTypeExtension'      => ['name', 'interfaces', 'directives', 'fields'],
        'InterfaceTypeExtension'   => ['name', 'directives', 'fields'],
        'UnionTypeExtension'       => ['name', 'directives', 'types'],
        'EnumTypeExtension'        => ['name', 'directives', 'values'],
        'InputObjectTypeExtension' => ['name', 'directives', 'fields'],
    ];
}
