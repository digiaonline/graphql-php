<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;

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
     *
     * @param array|VisitorInterface[] $visitors
     */
    public function __construct($visitors)
    {
        $this->visitors = $visitors;
    }

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        $newNode = null;

        foreach ($this->visitors as $i => $visitor) {
            if (!isset($this->_skipping[$i])) {
                try {
                    $newNode = $visitor->enterNode($node);
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
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface
    {
        $newNode = null;

        foreach ($this->visitors as $i => $visitor) {
            if (!isset($this->_skipping[$i])) {
                try {
                    $newNode = $visitor->leaveNode($node);
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
