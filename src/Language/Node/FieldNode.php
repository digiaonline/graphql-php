<?php

namespace Digia\GraphQL\Language\Node;

class FieldNode extends AbstractNode implements SelectionNodeInterface, DirectivesInterface
{
    use AliasTrait;
    use NameTrait;
    use ArgumentsTrait;
    use DirectivesTrait;
    use SelectionSetTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::FIELD;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'         => $this->kind,
            'loc'          => $this->getLocationAsArray(),
            'alias'        => $this->getAliasAsArray(),
            'name'         => $this->getNameAsArray(),
            'arguments'    => $this->getArgumentsAsArray(),
            'directives'   => $this->getDirectivesAsArray(),
            'selectionSet' => $this->getSelectionSetAsArray(),
        ];
    }
}
