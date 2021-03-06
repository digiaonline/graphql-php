<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class NameNode extends AbstractNode
{
    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::NAME;

    /**
     * NameNode constructor.
     *
     * @param mixed         $value
     * @param Location|null $location
     */
    public function __construct($value, ?Location $location)
    {
        parent::__construct(NodeKindEnum::NAME, $location);

        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'  => $this->kind,
            'value' => $this->value,
            'loc'   => $this->getLocationAST(),
        ];
    }
}
