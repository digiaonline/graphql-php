<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Util\SerializationInterface;

class KindVisitor extends Visitor
{

    /**
     * @var array
     */
    protected $enterKinds = [];

    /**
     * @var array
     */
    protected $leaveKinds = [];

    /**
     * KindVisitor constructor.
     * @param array $enterKinds
     * @param array $leaveKinds
     * @param callable|null $enterFunction
     * @param callable|null $leaveFunction
     */
    public function __construct(
        array $enterKinds,
        array $leaveKinds,
        ?callable $enterFunction = null,
        ?callable $leaveFunction = null
    ) {
        parent::__construct($enterFunction, $leaveFunction);

        $this->enterKinds = $enterKinds;
        $this->leaveKinds = $leaveKinds;
    }


    /**
     * @inheritdoc
     */
    public function enterNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface {
        if (\in_array($node->getKind(), $this->enterKinds, true)) {
            return parent::enterNode($node, $key, $parent, $path);
        }

        return $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface {
        if (\in_array($node->getKind(), $this->leaveKinds, true)) {
            return parent::leaveNode($node, $key, $parent, $path);
        }

        return $node;
    }
}
