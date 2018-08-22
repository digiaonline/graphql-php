<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class InlineFragmentNode extends AbstractNode implements
    FragmentNodeInterface,
    SelectionNodeInterface,
    SelectionSetAwareInterface
{
    use DirectivesTrait;
    use TypeConditionTrait;
    use SelectionSetTrait;

    /**
     * InlineFragmentNode constructor.
     *
     * @param NamedTypeNode|null    $typeCondition
     * @param DirectiveNode[]       $directives
     * @param SelectionSetNode|null $selectionSet
     * @param Location|null         $location
     */
    public function __construct(
        ?NamedTypeNode $typeCondition,
        array $directives,
        ?SelectionSetNode $selectionSet,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::INLINE_FRAGMENT, $location);

        $this->typeCondition = $typeCondition;
        $this->directives    = $directives;
        $this->selectionSet  = $selectionSet;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'          => $this->kind,
            'typeCondition' => $this->getTypeConditionAST(),
            'directives'    => $this->getDirectivesAST(),
            'selectionSet'  => $this->getSelectionSetAST(),
            'loc'           => $this->getLocationAST(),
        ];
    }
}
