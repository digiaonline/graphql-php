<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class FragmentSpreadNode extends AbstractNode implements FragmentNodeInterface, NameAwareInterface
{
    use NameTrait;
    use DirectivesTrait;
    use SelectionSetTrait;

    /**
     * FragmentSpreadNode constructor.
     *
     * @param NameNode              $name
     * @param DirectiveNode[]       $directives
     * @param SelectionSetNode|null $selectionSet
     * @param Location|null         $location
     */
    public function __construct(
        NameNode $name,
        array $directives,
        ?SelectionSetNode $selectionSet,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::FRAGMENT_SPREAD, $location);

        $this->name         = $name;
        $this->directives   = $directives;
        $this->selectionSet = $selectionSet;
    }
}
