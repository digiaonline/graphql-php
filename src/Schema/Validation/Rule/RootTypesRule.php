<?php

namespace Digia\GraphQL\Schema\Validation\Rule;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationTypeDefinitionNode;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use function Digia\GraphQL\Util\find;

class RootTypesRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function evaluate(): void
    {
        $schema = $this->context->getSchema();

        $rootTypes = [
            'query'        => $schema->getQueryType(),
            'mutation'     => $schema->getMutationType(),
            'subscription' => $schema->getSubscriptionType(),
        ];

        foreach ($rootTypes as $operation => $rootType) {
            $this->validateRootType($rootType, $operation);
        }
    }

    /**
     * @param NamedTypeInterface|ObjectType|null $rootType
     * @param string                             $operation
     */
    protected function validateRootType(?NamedTypeInterface $rootType, string $operation): void
    {
        $schema = $this->context->getSchema();

        if ($operation === 'query' && null === $rootType) {
            $this->context->reportError(
                new SchemaValidationException(
                    \sprintf('%s root type must be provided.', \ucfirst($operation)),
                    $schema->hasAstNode() ? [$schema->getAstNode()] : null
                )
            );

            return;
        }
    }
}
