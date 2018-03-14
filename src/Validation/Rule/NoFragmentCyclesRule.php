<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use function Digia\GraphQL\Validation\fragmentCycleMessage;

/**
 * No fragment cycles
 *
 * The graph of fragment spreads must not form any cycles including spreading itself.
 * Otherwise an operation could infinitely spread or infinitely execute on cycles in the underlying data.
 */
class NoFragmentCyclesRule extends AbstractRule
{
    /**
     * Tracks already visited fragments to maintain O(N) and to ensure that cycles
     * are not redundantly reported.
     *
     * @var array
     */
    protected $visitedFragments = [];

    /**
     * Array of AST nodes used to produce meaningful errors.
     *
     * @var array
     */
    protected $spreadPath = [];

    /**
     * Position in the spread path.
     *
     * @var array
     */
    protected $spreadPathIndexByName = [];

    /**
     * @inheritdoc
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): ?NodeInterface
    {
        return null; // Operations cannot contain fragments.
    }

    /**
     * @inheritdoc
     */
    protected function enterFragmentDefinition(FragmentDefinitionNode $node): ?NodeInterface
    {
        if (!isset($this->visitedFragments[$node->getNameValue()])) {
            $this->detectFragmentCycle($node);
        }

        return null;
    }

    /**
     * This does a straight-forward DFS to find cycles.
     * It does not terminate when a cycle was found but continues to explore
     * the graph to find all possible cycles.
     *
     * @param FragmentDefinitionNode $fragment
     */
    protected function detectFragmentCycle(FragmentDefinitionNode $fragment): void
    {
        $fragmentName = $fragment->getNameValue();

        $this->visitedFragments[$fragmentName] = true;

        $spreadNodes = $this->validationContext->getFragmentSpreads($fragment->getSelectionSet());

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
                    $spreadFragment = $this->validationContext->getFragment($spreadName);

                    if (null !== $spreadFragment) {
                        $this->detectFragmentCycle($spreadFragment);
                    }
                }

                \array_pop($this->spreadPath);
            } else {
                $cyclePath = \array_slice($this->spreadPath, $cycleIndex);

                $this->validationContext->reportError(
                    new ValidationException(
                        fragmentCycleMessage($spreadName, \array_map(function (FragmentSpreadNode $spread) {
                            return $spread->getNameValue();
                        }, $cyclePath)),
                        \array_merge($cyclePath, [$spreadNode])
                    )
                );
            }
        }

        $this->spreadPathIndexByName[$fragmentName] = null;
    }
}
