<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Util\SerializationInterface;

class ParallelVisitor implements VisitorInterface
{
    /**
     * @var array|VisitorInterface[]
     */
    protected $visitors;

    /**
     * @var array
     */
    protected $_skipping = [];

    /**
     * ParallelVisitor constructor.
     * @param array|VisitorInterface[] $visitors
     */
    public function __construct($visitors)
    {
        $this->visitors = $visitors;
    }

    /**
     * @param NodeInterface|AcceptVisitorTrait $node
     * @param string|int|null                  $key
     * @param NodeInterface|null               $parent
     * @param array                            $path
     * @return NodeInterface|SerializationInterface|null
     */
    public function enterNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface {
        $newNode = null;

        foreach ($this->visitors as $i => $visitor) {
            if (!isset($this->_skipping[$i])) {
                try {
                    $newNode = $visitor->enterNode($node, $key, $parent, $path);
                } catch (VisitorBreak $break) {
                    $this->_skipping[$i] = $break;
                    continue;
                }

                if (null === $newNode) {
                    $this->_skipping[$i] = $node;

                    $newNode = $node;
                }
            }
        }

        return $newNode;
    }

    /**
     * @param NodeInterface|AcceptVisitorTrait $node
     * @param string|int|null                  $key
     * @param NodeInterface|null               $parent
     * @param array                            $path
     * @return NodeInterface|SerializationInterface|null
     */
    public function leaveNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface {
        $newNode = null;

        foreach ($this->visitors as $i => $visitor) {
            if (!isset($this->_skipping[$i])) {
                try {
                    $newNode = $visitor->leaveNode($node, $key, $parent, $path);
                } catch (VisitorBreak $break) {
                    $this->_skipping[$i] = $break;
                    continue;
                }
            } elseif ($this->_skipping[$i] === $node) {
                unset($this->_skipping[$i]);
            }
        }

        return $newNode;
    }
}
