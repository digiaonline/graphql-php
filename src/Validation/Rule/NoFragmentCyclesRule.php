<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;

class NoFragmentCyclesRule extends AbstractRule
{
    /**
     * @var array
     */
    protected $visitedFragments = [];

    /**
     * @var array
     */
    protected $spreadPath = [];

    /**
     * @var array
     */
    protected $spreadPathIndexByName = [];

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            return null;
        }

        if ($node instanceof FragmentDefinitionNode) {
            if (!isset($this->visitedFragments[$node->getNameValue()])) {
                $this->detectFragmentCycle($node);
            }

            return null;
        }

        return $node;
    }

    /**
     * @param FragmentDefinitionNode $fragment
     */
    protected function detectFragmentCycle(FragmentDefinitionNode $fragment): void
    {
        $fragmentName = $fragment->getNameValue();

        $this->visitedFragments[$fragmentName] = true;

        $spreadNodes = $this->context->getFragmentSpreads($fragment->getSelectionSet());

        if (empty($spreadNodes)) {
            return;
        }

        $this->spreadPathIndexByName[$fragmentName] = \count($this->spreadPath);

        foreach ($spreadNodes as $spreadNode) {
            $spreadName = $spreadNode->getNameValue();
            $cycleIndex = $this->spreadPathIndexByName[$spreadName] ?? null;

            if (null === $cycleIndex) {
                $this->spreadPath[] = $spreadNode;

                if (!isset($this->visitedFragments[$spreadName])) {
                    $spreadFragment = $this->context->getFragment($spreadName);

                    if (null !== $spreadFragment) {
                        $this->detectFragmentCycle($spreadFragment);
                    }
                }

                array_pop($this->spreadPath);
            } else {
                $cyclePath = \array_slice($this->spreadPath, $cycleIndex);

                $this->context->reportError(
                    new ValidationException(
                        fragmentCycleMessage($spreadName, array_map(function (FragmentSpreadNode $spread) {
                            return $spread->getNameValue();
                        }, $cyclePath)),
                        array_merge($cyclePath, [$spreadNode])
                    )
                );
            }
        }

        $this->spreadPathIndexByName[$fragmentName] = null;
    }
}
