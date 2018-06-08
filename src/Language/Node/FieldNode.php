<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class FieldNode extends AbstractNode implements SelectionNodeInterface, ArgumentsAwareInterface,
    DirectivesAwareInterface, NameAwareInterface
{
    use NameTrait;
    use AliasTrait;
    use ArgumentsTrait;
    use DirectivesTrait;
    use SelectionSetTrait;

    /**
     * FieldNode constructor.
     *
     * @param NameNode|null         $alias
     * @param NameNode              $name
     * @param ArgumentNode[]        $arguments
     * @param DirectiveNode[]       $directives
     * @param SelectionSetNode|null $selectionSet
     * @param Location|null         $location
     */
    public function __construct(
        ?NameNode $alias,
        NameNode $name,
        array $arguments,
        array $directives,
        ?SelectionSetNode $selectionSet,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::FIELD, $location);

        $this->alias        = $alias;
        $this->name         = $name;
        $this->arguments    = $arguments;
        $this->directives   = $directives;
        $this->selectionSet = $selectionSet;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'         => $this->kind,
            'loc'          => $this->getLocationAST(),
            'alias'        => $this->getAliasAST(),
            'name'         => $this->getNameAST(),
            'arguments'    => $this->getArgumentsAST(),
            'directives'   => $this->getDirectivesAST(),
            'selectionSet' => $this->getSelectionSetAST(),
        ];
    }
}
