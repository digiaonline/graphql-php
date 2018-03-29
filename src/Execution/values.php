<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Node\ArgumentsAwareInterface;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\SchemaInterface;

/**
 * @param SchemaInterface          $schema
 * @param VariableDefinitionNode[] $nodes
 * @param array                    $inputs
 * @return array
 */
//function getVariableValues(SchemaInterface $schema, array $nodes, array $inputs): array
//{
//    return GraphQL::make(ExecutionHelper::class)->getVariableValues($schema, $nodes, $inputs);
//}

/**
 * @param Field|Directive         $definition
 * @param ArgumentsAwareInterface $node
 * @param array                   $variableValues
 * @return array
 */
function getArgumentValues($definition, ArgumentsAwareInterface $node, array $variableValues = []): array
{
    return GraphQL::make(ValuesHelper::class)->getArgumentValues($definition, $node, $variableValues);
}

/**
 * @param DirectiveInterface $directive
 * @param mixed              $node
 * @param array              $variableValues
 * @return array|null
 */
function getDirectiveValues(Directive $directive, $node, array $variableValues = []): ?array
{
    return GraphQL::make(ValuesHelper::class)->getDirectiveValues($directive, $node, $variableValues);
}
