<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\doTypesOverlap;
use function Digia\GraphQL\Util\typeFromAST;
use function Digia\GraphQL\Validation\typeIncompatibleAnonymousSpreadMessage;
use function Digia\GraphQL\Validation\typeIncompatibleSpreadMessage;

/**
 * Possible fragment spread
 *
 * A fragment spread is only valid if the type condition could ever possibly
 * be true: if there is a non-empty intersection of the possible parent types,
 * and possible types which pass the type condition.
 */
class PossibleFragmentSpreadsRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof InlineFragmentNode) {
            $fragmentType = $this->validationContext->getType();
            $parentType   = $this->validationContext->getParentType();

            if ($fragmentType instanceof CompositeTypeInterface &&
                $parentType instanceof CompositeTypeInterface &&
                !doTypesOverlap($this->validationContext->getSchema(), $fragmentType, $parentType)) {
                $this->validationContext->reportError(
                    new ValidationException(
                        typeIncompatibleAnonymousSpreadMessage($parentType, $fragmentType),
                        [$node]
                    )
                );
            }
        }

        if ($node instanceof FragmentSpreadNode) {
            $fragmentName = $node->getNameValue();
            $fragmentType = $this->getFragmentType($fragmentName);
            $parentType   = $this->validationContext->getParentType();

            if (null !== $fragmentType &&
                null !== $parentType &&
                !doTypesOverlap($this->validationContext->getSchema(), $fragmentType, $parentType)) {
                $this->validationContext->reportError(
                    new ValidationException(
                        typeIncompatibleSpreadMessage($fragmentName, $parentType, $fragmentType),
                        [$node]
                    )
                );
            }
        }

        return $node;
    }

    /**
     * @param string $name
     * @return TypeInterface|null
     * @throws InvalidTypeException
     */
    protected function getFragmentType(string $name): ?TypeInterface
    {
        $fragment = $this->validationContext->getFragment($name);

        if (null === $fragment) {
            return null;
        }

        $type = typeFromAST($this->validationContext->getSchema(), $fragment->getTypeCondition());

        return $type instanceof CompositeTypeInterface ? $type : null;
    }
}
