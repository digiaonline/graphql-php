<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class NameNode extends AbstractNode implements NodeInterface
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
    public function toArray(): array
    {
        return [
            'kind'  => $this->kind,
            'value' => $this->value,
            'loc'   => $this->getLocationAsArray(),
        ];
    }
}
