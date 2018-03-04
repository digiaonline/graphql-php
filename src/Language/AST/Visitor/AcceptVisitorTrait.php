<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

trait AcceptVisitorTrait
{

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

    /**
     * @var VisitorInterface
     */
    protected $visitor;

    /**
     * @var array
     */
    protected $path;

    /**
     * @var array
     */
    protected $edited = [];

    /**
     * @return string
     */
    abstract public function getKind(): string;

    /**
     * @param VisitorInterface $visitor
     * @param string|null $key
     * @return array|null
     */
    public function accept(VisitorInterface $visitor, ?string $key = null, array $path = []): ?array
    {
        $this->visitor = $visitor;
        $this->path = $path;

        /** @noinspection PhpParamsInspection */
        if (null === ($enterResult = $visitor->enterNode($this->toArray(), $key, $this->path))) {
            return null;
        }

        $this->edited = $enterResult;

        foreach (self::$kindToNodesToVisitMap[$this->getKind()] as $name) {
            // We have to remove children ensure that we do not include nodes in the result
            // even though we return null from either enterNode or leaveNode.
            unset($this->edited[$name]);

            /** @var NodeInterface|NodeInterface[] $nodeOrNodes */
            $nodeOrNodes = $this->{$name};

            if (\is_array($nodeOrNodes) && !empty($nodeOrNodes)) {
                $this->visitNodes($nodeOrNodes, $name);
            } elseif ($nodeOrNodes instanceof AcceptVisitorInterface) {
                $this->visitNode($nodeOrNodes, $name);
            }
        }

        /** @noinspection PhpParamsInspection */
        if (null === ($leaveResult = $visitor->leaveNode($this->toArray(), $key, $this->path))) {
            return null;
        }

        return array_merge($leaveResult, $this->edited);
    }

    /**
     * @param NodeInterface[] $nodes
     * @param string $key
     */
    protected function visitNodes(array $nodes, string $key)
    {
        $this->addOneToPath($key);

        $index = 0;

        foreach ($nodes as $node) {
            if ($node instanceof AcceptVisitorInterface) {
                $this->addOneToPath($index);

                if (null !== ($result = $this->acceptVisitor($node, $index))) {
                    $this->edited[$key][$index] = $result;
                    $index++;
                }

                $this->removeOneFromPath();
            }
        }

        $this->removeOneFromPath();
    }

    /**
     * @param NodeInterface $node
     * @param null|string $key
     */
    protected function visitNode(NodeInterface $node, ?string $key = null)
    {
        $this->addOneToPath($key);

        if (null !== ($result = $this->acceptVisitor($node, $key))) {
            $this->edited[$key] = $result;
        }

        $this->removeOneFromPath();
    }

    /**
     * @param NodeInterface|AcceptVisitorInterface $node
     * @param string|null $key
     * @param array $path
     * @return array|null
     */
    protected function acceptVisitor(NodeInterface $node, ?string $key): ?array
    {
        if (null === ($result = $node->accept($this->visitor, $key, $this->path))) {
            return null;
        }

        return $result;
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
}
