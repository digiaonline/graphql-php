<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Util\SerializationInterface;

trait AcceptVisitorTrait
{

    /**
     * @var VisitorInterface
     */
    protected $visitor;

    /**
     * @var array
     */
    protected $path;

    /**
     * @param VisitorInterface $visitor
     * @param string|null $key
     * @param array $path
     * @return NodeInterface|SerializationInterface|null
     */
    public function accept(VisitorInterface $visitor, ?string $key = null, array $path = []): ?NodeInterface
    {
        $this->visitor = $visitor;
        $this->path = $path;

        // TODO: Benchmark
        /** @var NodeInterface|AcceptVisitorTrait $newNode */
        $newNode = clone $this;

        if (null === ($newNode = $visitor->enterNode($newNode, $key, $this->path))) {
            return null;
        }

        foreach (self::$kindToNodesToVisitMap[$this->kind] as $name) {
            $nodeOrNodes = $this->getNodeOrNodes($name);

            if (null === $nodeOrNodes || empty($nodeOrNodes)) {
                continue;
            }

            $newNodeOrNodes = $this->visitNodeOrNodes($nodeOrNodes, $name);

            if (null === $newNodeOrNodes || empty($newNodeOrNodes)) {
                continue;
            }

            $setter = 'set' . ucfirst($name);

            if (method_exists($newNode, $setter)) {
                $newNode->{$setter}($newNodeOrNodes);
            }
        }

        if (null === ($newNode = $visitor->leaveNode($newNode, $key, $this->path))) {
            return null;
        }

        return $newNode;
    }

    /**
     * @param string $key
     * @return array|NodeInterface|NodeInterface[]|null
     */
    protected function getNodeOrNodes(string $key)
    {
        return $this->{$key};
    }

    /**
     * @param $nodeOrNodes
     * @param string $key
     * @return array|NodeInterface|NodeInterface[]|null
     */
    protected function visitNodeOrNodes($nodeOrNodes, string $key)
    {
        return \is_array($nodeOrNodes) ? $this->visitNodes($nodeOrNodes, $key) : $this->visitNode($nodeOrNodes, $key);
    }

    /**
     * @param NodeInterface[] $nodes
     * @param string $key
     * @return NodeInterface[]
     */
    protected function visitNodes(array $nodes, string $key): array
    {
        $this->addOneToPath($key);

        $index = 0;
        $newNodes = [];

        foreach ($nodes as $node) {
            if (null !== ($newNode = $this->visitNode($node, $index))) {
                $newNodes[$index] = $newNode;
                $index++;
            }
        }

        $this->removeOneFromPath();

        return $newNodes;
    }

    /**
     * @param NodeInterface|AcceptVisitorTrait $node
     * @param string $key
     * @return NodeInterface|null
     */
    protected function visitNode(NodeInterface $node, string $key): ?NodeInterface
    {
        $this->addOneToPath($key);

        $newNode = $node->accept($this->visitor, $key, $this->path);

        $this->removeOneFromPath();

        return $newNode;
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
        $this->path = \array_slice($this->path, 0, count($this->path) - 1);
    }

    /**
     * @var array
     */
    protected static $kindToNodesToVisitMap = [
        'Name' => [],

        'Document' => ['definitions'],
        'OperationDefinition' => [
            'name',
            'variableDefinitions',
            'directives',
            'selectionSet',
        ],
        'VariableDefinition' => ['variable', 'type', 'defaultValue'],
        'Variable' => ['name'],
        'SelectionSet' => ['selections'],
        'Field' => ['alias', 'name', 'arguments', 'directives', 'selectionSet'],
        'Argument' => ['name', 'value'],

        'FragmentSpread' => ['name', 'directives'],
        'InlineFragment' => ['typeCondition', 'directives', 'selectionSet'],
        'FragmentDefinition' => [
            'name',
            // Note: fragment variable definitions are experimental and may be changed or removed in the future.
            'variableDefinitions',
            'typeCondition',
            'directives',
            'selectionSet',
        ],

        'IntValue' => [],
        'FloatValue' => [],
        'StringValue' => [],
        'BooleanValue' => [],
        'NullValue' => [],
        'EnumValue' => [],
        'ListValue' => ['values'],
        'ObjectValue' => ['fields'],
        'ObjectField' => ['name', 'value'],

        'Directive' => ['name', 'arguments'],

        'NamedType' => ['name'],
        'ListType' => ['type'],
        'NonNullType' => ['type'],

        'SchemaDefinition' => ['directives', 'operationTypes'],
        'OperationTypeDefinition' => ['type'],

        'ScalarTypeDefinition' => ['description', 'name', 'directives'],
        'ObjectTypeDefinition' => [
            'description',
            'name',
            'interfaces',
            'directives',
            'fields',
        ],
        'FieldDefinition' => ['description', 'name', 'arguments', 'type', 'directives'],
        'InputValueDefinition' => [
            'description',
            'name',
            'type',
            'defaultValue',
            'directives',
        ],
        'InterfaceTypeDefinition' => ['description', 'name', 'directives', 'fields'],
        'UnionTypeDefinition' => ['description', 'name', 'directives', 'types'],
        'EnumTypeDefinition' => ['description', 'name', 'directives', 'values'],
        'EnumValueDefinition' => ['description', 'name', 'directives'],
        'InputObjectTypeDefinition' => ['description', 'name', 'directives', 'fields'],

        'ScalarTypeExtension' => ['name', 'directives'],
        'ObjectTypeExtension' => ['name', 'interfaces', 'directives', 'fields'],
        'InterfaceTypeExtension' => ['name', 'directives', 'fields'],
        'UnionTypeExtension' => ['name', 'directives', 'types'],
        'EnumTypeExtension' => ['name', 'directives', 'values'],
        'InputObjectTypeExtension' => ['name', 'directives', 'fields'],

        'DirectiveDefinition' => ['description', 'name', 'arguments', 'locations'],
    ];
}
