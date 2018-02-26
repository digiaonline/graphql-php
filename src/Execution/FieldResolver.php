<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Execution\Contract\ResolverInterface;

class FieldResolver implements ResolverInterface
{

    /**
     * @var array
     */
    protected $resolvers;

    /**
     * FrontResolver constructor.
     *
     * @param $resolvers
     */
    public function __construct($resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function resolve($source, $args, $context, ResolveInfo $info)
    {
        $fieldName = $info->getFieldName();

        if (!isset($this->resolvers[$fieldName])) {
            throw new \Exception(sprintf('Cannot find resolver for field: %s', $fieldName));
        }

        $resolver = $this->resolvers[$fieldName];

        if (!$resolver instanceof ResolverInterface) {
            throw new \Exception(sprintf('Resolvers must implement %s', ResolverInterface::class));
        }

        $resolver->resolve($source, $args, $context, $info);
    }
}
