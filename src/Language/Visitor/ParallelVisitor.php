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
    public function enterNode(NodeInterface $node): VisitorResult
    {
        foreach ($this->visitors as $i => $visitor) {
            if (!isset($this->skipping[$i])) {
                $VisitorResult = $visitor->enterNode($node);

                if ($VisitorResult->getAction() === VisitorResult::ACTION_BREAK) {
                    $this->skipping[$i] = true;
                    continue;
                }

                if (null === $VisitorResult->getValue()) {
                    $this->skipping[$i] = $node;

                    $VisitorResult = new VisitorResult($node);
                }
            }
        }

        return $VisitorResult ?? new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): VisitorResult
    {
        foreach ($this->visitors as $i => $visitor) {
            if (!isset($this->skipping[$i])) {
                $VisitorResult = $visitor->leaveNode($node);

                if ($VisitorResult->getAction() === VisitorResult::ACTION_BREAK) {
                    $this->skipping[$i] = true;
                    continue;
                }
            } elseif ($this->skipping[$i] === $node) {
                unset($this->skipping[$i]);
            }
        }

        return $VisitorResult ?? new VisitorResult(null);
    }
}
