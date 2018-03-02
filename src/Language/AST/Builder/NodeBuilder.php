<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

class NodeBuilder implements NodeBuilderInterface, DirectorInterface
{

    /**
     * @var BuilderInterface[]
     */
    protected $builders;

    /**
     * NodeBuilder constructor.
     *
     * @param BuilderInterface[] $builders
     */
    public function __construct($builders)
    {
        foreach ($builders as $builder) {
            $builder->setDirector($this);
        }

        $this->builders = $builders;
    }

    /**
     * @param array $ast
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function build(array $ast): NodeInterface
    {
        if (!isset($ast['kind'])) {
            throw new GraphQLError(sprintf('Nodes must specify a kind, got %s', json_encode($ast)));
        }

        $builder = $this->getBuilder($ast['kind']);

        if ($builder !== null) {
            return $builder->build($ast);
        }

        throw new GraphQLError(sprintf('Node of kind "%s" not supported.', $ast['kind']));
    }

    /**
     * @param string $kind
     * @return BuilderInterface|null
     */
    protected function getBuilder(string $kind): ?BuilderInterface
    {
        foreach ($this->builders as $builder) {
            if ($builder instanceof BuilderInterface && $builder->supportsKind($kind)) {
                return $builder;
            }
        }

        return null;
    }
}
