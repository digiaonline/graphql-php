<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use GraphQL\Contracts\TypeSystem\Type\CompositeTypeInterface;
use Digia\GraphQL\Util\TypeASTConverter;
use Digia\GraphQL\Validation\ValidationException;
use function Digia\GraphQL\Validation\fragmentOnNonCompositeMessage;
use function Digia\GraphQL\Validation\inlineFragmentOnNonCompositeMessage;


/**
 * Fragments on composite type
 *
 * Fragments use a type condition to determine if they apply, since fragments
 * can only be spread into a composite type (object, interface, or union), the
 * type condition must also be a composite type.
 */
class FragmentsOnCompositeTypesRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    protected function enterFragmentDefinition(FragmentDefinitionNode $node): VisitorResult
    {
        $this->validateFragementNode($node, function (FragmentDefinitionNode $node) {
            return fragmentOnNonCompositeMessage((string)$node, (string)$node->getTypeCondition());
        });

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function enterInlineFragment(InlineFragmentNode $node): VisitorResult
    {
        $this->validateFragementNode($node, function (InlineFragmentNode $node) {
            return inlineFragmentOnNonCompositeMessage((string)$node->getTypeCondition());
        });

        return new VisitorResult($node);
    }


    /**
     * @param InlineFragmentNode|FragmentDefinitionNode $node
     * @param callable                                  $errorMessageFunction
     * @throws \Exception
     * @throws \TypeError
     */
    protected function validateFragementNode($node, callable $errorMessageFunction)
    {
        $typeCondition = $node->getTypeCondition();

        if (null !== $typeCondition) {
            $type = TypeASTConverter::convert($this->context->getSchema(), $typeCondition);

            if (null !== $type && !($type instanceof CompositeTypeInterface)) {
                $this->context->reportError(
                    new ValidationException($errorMessageFunction($node),
                        [$typeCondition]
                    )
                );
            }
        }
    }
}
