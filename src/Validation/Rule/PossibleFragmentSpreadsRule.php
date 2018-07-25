<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ConversionException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Util\TypeASTConverter;
use Digia\GraphQL\Util\TypeHelper;
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
     *
     * @throws InvariantException
     */
    protected function enterInlineFragment(InlineFragmentNode $node): ?NodeInterface
    {
        $fragmentType = $this->context->getType();
        $parentType   = $this->context->getParentType();

        if ($fragmentType instanceof CompositeTypeInterface &&
            $parentType instanceof CompositeTypeInterface &&
            !TypeHelper::doTypesOverlap($this->context->getSchema(),
                $fragmentType, $parentType)) {
            $this->context->reportError(
                new ValidationException(
                    typeIncompatibleAnonymousSpreadMessage($parentType, $fragmentType),
                    [$node]
                )
            );
        }

        return $node;
    }

    /**
     * @inheritdoc
     *
     * @throws InvariantException
     * @throws ConversionException
     */
    protected function enterFragmentSpread(FragmentSpreadNode $node): ?NodeInterface
    {
        $fragmentName = $node->getNameValue();
        $fragmentType = $this->getFragmentType($fragmentName);
        $parentType   = $this->context->getParentType();

        if (null !== $fragmentType &&
            null !== $parentType &&
            !TypeHelper::doTypesOverlap($this->context->getSchema(),
                $fragmentType, $parentType)) {
            $this->context->reportError(
                new ValidationException(
                    typeIncompatibleSpreadMessage($fragmentName, $parentType, $fragmentType),
                    [$node]
                )
            );
        }

        return $node;
    }

    /**
     * @param string $name
     * @return TypeInterface|null
     * @throws InvariantException
     * @throws ConversionException
     */
    protected function getFragmentType(string $name): ?TypeInterface
    {
        $fragment = $this->context->getFragment($name);

        if (null === $fragment) {
            return null;
        }

        $type = TypeASTConverter::convert($this->context->getSchema(), $fragment->getTypeCondition());

        return $type instanceof CompositeTypeInterface ? $type : null;
    }
}
