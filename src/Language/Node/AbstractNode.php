<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Language\NodeBuilderInterface;
use Digia\GraphQL\Language\Visitor\VisitorBreak;
use Digia\GraphQL\Language\Visitor\VisitorInterface;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Util\ArrayToJsonTrait;
use Digia\GraphQL\Util\SerializationInterface;

abstract class AbstractNode implements NodeInterface, SerializationInterface
{
    use ArrayToJsonTrait;

    /**
     * @var string
     */
    protected $kind;

    /**
     * @var Location|null
     */
    protected $location;

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
     * @var NodeBuilderInterface
     */
    private static $nodeBuilder;

    /**
     * @return array
     */
    abstract public function toAST(): array;

    /**
     * AbstractNode constructor.
     *
     * @param string        $kind
     * @param Location|null $location
     */
    public function __construct(string $kind, ?Location $location)
    {
        $this->kind     = $kind;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * @return bool
     */
    public function hasLocation(): bool
    {
        return null !== $this->location;
    }

    /**
     * @return Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @return array|null
     */
    public function getLocationAST(): ?array
    {
        return null !== $this->location
            ? $this->location->toArray()
            : null;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->toAST();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJSON();
    }

    /**
     * @inheritdoc
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

        $VisitorResult = $visitor->enterNode(clone $this);
        $newNode       = $VisitorResult->getValue();

        // Handle early exit while entering
        if ($VisitorResult->getAction() === VisitorResult::ACTION_BREAK) {
            throw new VisitorBreak();
        }

        // If the result was null, it means that we should not traverse this branch.
        if (null === $newNode) {
            return null;
        }

        // If the node was edited, we want to return early to avoid visiting its sub-tree completely.
        if ($newNode->determineIsEdited($this)) {
            return $newNode;
        }

        foreach (self::$kindToNodesToVisitMap[$this->kind] as $property) {
            $nodeOrNodes = $this->{$property};

            if (empty($nodeOrNodes)) {
                continue;
            }

            $newNodeOrNodes = $this->visitNodeOrNodes($nodeOrNodes, $property, $newNode);

            if (empty($newNodeOrNodes)) {
                continue;
            }

            $setter = 'set' . \ucfirst($property);

            if (\method_exists($newNode, $setter)) {
                $newNode->{$setter}($newNodeOrNodes);
            }
        }

        $VisitorResult = $visitor->leaveNode($newNode);

        // Handle early exit while leaving
        if ($VisitorResult->getAction() === VisitorResult::ACTION_BREAK) {
            throw new VisitorBreak();
        }

        return $VisitorResult->getValue();
    }

    /**
     * @inheritdoc
     */
    public function determineIsEdited(NodeInterface $node): bool
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
     * @return NodeInterface|NodeInterface[]|null
     */
    protected function visitNodeOrNodes($nodeOrNodes, $key, NodeInterface $parent)
    {
        $this->addAncestor($parent);

        $newNodeOrNodes = \is_array($nodeOrNodes)
            ? $this->visitNodes($nodeOrNodes, $key)
            : $this->visitNode($nodeOrNodes, $key, $parent);

        $this->removeAncestor();

        return $newNodeOrNodes;
    }

    /**
     * @param NodeInterface[] $nodes
     * @param string|int      $key
     * @return NodeInterface[]
     */
    protected function visitNodes(array $nodes, $key): array
    {
        $this->addOneToPath($key);

        $index    = 0;
        $newNodes = [];

        foreach ($nodes as $node) {
            $newNode = $this->visitNode($node, $index, null);

            if (null !== $newNode) {
                $newNodes[$index] = $newNode;
                $index++;
            }
        }

        $this->removeOneFromPath();

        return $newNodes;
    }

    /**
     * @param NodeInterface      $node
     * @param string|int         $key
     * @param NodeInterface|null $parent
     * @return NodeInterface|null
     */
    protected function visitNode(NodeInterface $node, $key, ?NodeInterface $parent): ?NodeInterface
    {
        $this->addOneToPath($key);

        $newNode = $node->acceptVisitor($this->visitor, $key, $parent, $this->path, $this->ancestors);

        // If the node was edited, we need to revisit it
        // to produce the expected result.
        if (null !== $newNode && $newNode->isEdited()) {
            $newNode = $newNode->acceptVisitor($this->visitor, $key, $parent, $this->path, $this->ancestors);
        }

        $this->removeOneFromPath();

        return $newNode;
    }

    /**
     * @param NodeInterface $other
     * @return bool
     */
    protected function compareNode(NodeInterface $other)
    {
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
        \array_pop($this->path);
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
            self::$nodeBuilder = GraphQL::make(NodeBuilderInterface::class);
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
