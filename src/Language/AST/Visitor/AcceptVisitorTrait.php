<?php

namespace Digia\GraphQL\Language\AST\Visitor;

trait AcceptVisitorTrait
{

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
            // Note: fragment variable definitions are experimental and may be changed
            // or removed in the future.
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
        /** @noinspection PhpParamsInspection */
        if (null === ($enterResult = $visitor->enterNode($this, $key, $path))) {
            return null;
        }

        $edited = $enterResult;
        foreach (self::$kindToNodesToVisitMap[$this->getKind()] as $name) {
            unset($edited[$name]);
            $value = $this->{$name};
            if (\is_array($value) && !empty($value)) {
                $path[] = $name;
                $i = 0;
                foreach ($value as $v) {
                    if ($v instanceof AcceptVisitorInterface) {
                        $path[] = $i;
                        if (null !== ($result = $v->accept($visitor, $i, $path))) {
                            $edited[$name][$i] = $result;
                            $i++;
                        }
                        $path = \array_slice($path, 0, count($path) - 1);
                    }
                }
                $path = \array_slice($path, 0, count($path) - 1);
            } elseif ($value instanceof AcceptVisitorInterface) {
                $path[] = $name;
                if (null !== ($result = $value->accept($visitor, $name, $path))) {
                    $edited[$name] = $result;
                }
                $path = \array_slice($path, 0, count($path) - 1);
            }
        }

        /** @noinspection PhpParamsInspection */
        if (null === ($leaveResult = $visitor->leaveNode($this, $key, $path))) {
            return null;
        }

        return array_merge($leaveResult, \is_array($edited) ? $edited : []);
    }
}
