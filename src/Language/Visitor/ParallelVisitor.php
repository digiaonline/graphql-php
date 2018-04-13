<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;

class ParallelVisitor implements VisitorInterface
{
    /**
     * @var VisitorInterface[]
     */
    protected $visitors;

    /**
     * @var array
     */
    protected $skipping = [];

    /**
     * ParallelVisitor constructor.
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
            if (!isset($this->skipping[$i])) {
                try {
                    $newNode = $visitor->enterNode($node);
                } catch (VisitorBreak $break) {
                    $this->skipping[$i] = $break;
                    continue;
                }

                if (null === $newNode) {
                    $this->skipping[$i] = $node;

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
            if (!isset($this->skipping[$i])) {
                try {
                    $newNode = $visitor->leaveNode($node);
                } catch (VisitorBreak $break) {
                    $this->skipping[$i] = $break;
                    continue;
                }
            } elseif ($this->skipping[$i] === $node) {
                unset($this->skipping[$i]);
            }
        }

        return $newNode;
    }
}
