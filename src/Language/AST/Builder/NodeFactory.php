<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Contract\NodeBuilderInterface;
use Digia\GraphQL\Language\AST\Builder\Contract\NodeFactoryInterface;
use Digia\GraphQL\Language\AST\Exception\NodeNotSupportedException;
use Digia\GraphQL\Language\AST\Location;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

class NodeFactory implements NodeFactoryInterface
{

    /**
     * @var array|NodeBuilderInterface[]
     */
    protected $builders = [];

    /**
     * NodeFactory constructor.
     *
     * @param NodeBuilderInterface[] $builders
     */
    public function __construct(array $builders = [])
    {
        foreach ($builders as $builder) {
            $builder->setFactory($this);
        }

        $this->builders = $builders;
    }

    /**
     * @param array $ast
     * @return NodeInterface
     * @throws NodeNotSupportedException
     */
    public function build(array $ast): NodeInterface
    {
        if (!isset($ast['kind'])) {
            throw new NodeNotSupportedException('Nodes must specify a kind.');
        }

        $builder = $this->getBuilder($ast['kind']);

        if ($builder !== null) {
            return $builder->build($ast);
        }

        throw new NodeNotSupportedException(sprintf('Node of kind "%s" not supported.', $ast['kind']));
    }

    /**
     * @param array $ast
     * @return Location
     */
    public function createLocation(array $ast): Location
    {
        return new Location([
            'start' => $ast['start'],
            'end'   => $ast['end'],
        ]);
    }

    /**
     * @param string $kind
     * @return NodeBuilderInterface|null
     */
    protected function getBuilder(string $kind): ?NodeBuilderInterface
    {
        foreach ($this->builders as $builder) {
            if ($builder instanceof NodeBuilderInterface && $builder->supportsKind($kind)) {
                return $builder;
            }
        }

        return null;
    }
}
