<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\InlineFragmentNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use function Digia\GraphQL\Util\typeFromAST;

class FragmentsOnCompositeTypesRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface {
        if ($node instanceof InlineFragmentNode) {
            $this->validateFragementNode($node, function (NodeInterface $node) {
                /** @noinspection PhpUndefinedMethodInspection */
                return inlineFragmentOnNonCompositeErrorMessage($node->getTypeCondition()->getNameValue());
            });
        }

        if ($node instanceof FragmentDefinitionNode) {
            $this->validateFragementNode($node, function (NodeInterface $node) {
                /** @noinspection PhpUndefinedMethodInspection */
                return fragmentOnNonCompositeMessage($node->getNameValue(), $node->getTypeCondition()->getNameValue());
            });
        }

        return $node;
    }

    /**
     * @param NodeInterface|InlineFragmentNode|FragmentDefinitionNode $node
     * @param callable                                                $errorMessageFunction
     * @throws \Exception
     * @throws \TypeError
     */
    protected function validateFragementNode(NodeInterface $node, callable $errorMessageFunction)
    {
        $typeCondition = $node->getTypeCondition();

        if (null !== $typeCondition) {
            $type = typeFromAST($this->context->getSchema(), $typeCondition);

            if (null !== $type && !($type instanceof CompositeTypeInterface)) {
                $this->context->reportError(new GraphQLError($errorMessageFunction($node), [$typeCondition]));
            }
        }
    }
}
