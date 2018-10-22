<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Language\NodeBuilderInterface;
use Digia\GraphQL\Language\Visitor\VisitorBreak;
use Digia\GraphQL\Language\Visitor\VisitorInfo;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Util\ArrayToJsonTrait;
use Digia\GraphQL\Util\NodeComparator;
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
     * @var VisitorInfo|null
     */
    protected $visitorInfo;

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
    public function acceptVisitor(VisitorInfo $visitorInfo): ?NodeInterface
    {
        $this->visitorInfo = $visitorInfo;

        $visitor       = $this->visitorInfo->getVisitor();
        $VisitorResult = $visitor->enterNode(clone $this);
        $newNode       = $VisitorResult->getValue();

        // Handle early exit while entering
        if ($VisitorResult->getAction() === VisitorResult::ACTION_BREAK) {
            /** @noinspection PhpUnhandledExceptionInspection */
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
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new VisitorBreak();
        }

        return $VisitorResult->getValue();
    }

    /**
     * @return VisitorInfo|null
     */
    public function getVisitorInfo(): ?VisitorInfo
    {
        return $this->visitorInfo;
    }

    /**
     * @inheritdoc
     */
    public function determineIsEdited(NodeInterface $node): bool
    {
        return $this->isEdited = $this->isEdited() || !NodeComparator::compare($this, $node);
    }

    /**
     * @inheritdoc
     */
    public function getAncestor(int $depth = 1): ?NodeInterface
    {
        return null !== $this->visitorInfo ? $this->visitorInfo->getAncestor($depth) : null;
    }

    /**
     * @return bool
     */
    public function isEdited(): bool
    {
        return $this->isEdited;
    }

    /**
     * @param NodeInterface|NodeInterface[] $nodeOrNodes
     * @param mixed                         $key
     * @param NodeInterface                 $parent
     * @return NodeInterface|NodeInterface[]|null
     */
    protected function visitNodeOrNodes($nodeOrNodes, $key, NodeInterface $parent)
    {
        $this->visitorInfo->addAncestor($parent);

        $newNodeOrNodes = \is_array($nodeOrNodes)
            ? $this->visitNodes($nodeOrNodes, $key)
            : $this->visitNode($nodeOrNodes, $key, $parent);

        $this->visitorInfo->removeAncestor();

        return $newNodeOrNodes;
    }

    /**
     * @param NodeInterface[] $nodes
     * @param string|int      $key
     * @return NodeInterface[]
     */
    protected function visitNodes(array $nodes, $key): array
    {
        $this->visitorInfo->addOneToPath($key);

        $index    = 0;
        $newNodes = [];

        foreach ($nodes as $node) {
            $newNode = $this->visitNode($node, $index, null);

            if (null !== $newNode) {
                $newNodes[$index] = $newNode;
                $index++;
            }
        }

        $this->visitorInfo->removeOneFromPath();

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
        $this->visitorInfo->addOneToPath($key);

        $info = new VisitorInfo(
            $this->visitorInfo->getVisitor(),
            $key,
            $parent,
            $this->visitorInfo->getPath(),
            $this->visitorInfo->getAncestors()
        );

        $newNode = $node->acceptVisitor($info);

        // If the node was edited, we need to revisit it to produce the expected result.
        if (null !== $newNode && $newNode->isEdited()) {
            $newNode = $newNode->acceptVisitor($info);
        }

        $this->visitorInfo->removeOneFromPath();

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
