<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class DirectiveNode extends AbstractNode implements ArgumentsAwareInterface, NameAwareInterface
{
    use NameTrait;
    use ArgumentsTrait;

    /**
     * DirectiveNode constructor.
     *
     * @param NameNode       $name
     * @param ArgumentNode[] $arguments
     * @param Location|null  $location
     */
    public function __construct(NameNode $name, array $arguments, ?Location $location)
    {
        parent::__construct(NodeKindEnum::DIRECTIVE, $location);

        $this->name      = $name;
        $this->arguments = $arguments;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'      => $this->kind,
            'name'      => $this->getNameAST(),
            'arguments' => $this->getArgumentsAST(),
            'loc'       => $this->getLocationAST(),
        ];
    }
}
