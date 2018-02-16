<?php

namespace Digia\GraphQL\Language\AST\Builder\Contract;

use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

interface NodeBuilderInterface
{

    /**
     * @param array $ast
     * @return NodeInterface
     */
    public function build(array $ast): NodeInterface;

    /**
     * @param string $kind
     * @return bool
     */
    public function supportsKind(string $kind): bool;

    /**
     * @param NodeFactoryInterface $factory
     * @return $this
     */
    public function setFactory(NodeFactoryInterface $factory);
}
