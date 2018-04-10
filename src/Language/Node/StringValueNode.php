<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class StringValueNode extends AbstractNode implements ValueNodeInterface
{
    use ValueTrait;

    /**
     * @var bool
     */
    protected $block;

    /**
     * StringValueNode constructor.
     *
     * @param mixed         $value
     * @param bool          $block
     * @param Location|null $location
     */
    public function __construct($value, bool $block, ?Location $location)
    {
        parent::__construct(NodeKindEnum::STRING, $location);

        $this->value = $value;
        $this->block = $block;
    }

    /**
     * @return bool
     */
    public function isBlock(): bool
    {
        return $this->block;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'  => $this->kind,
            'loc'   => $this->getLocationAsArray(),
            'block' => $this->block,
            'value' => $this->value,
        ];
    }
}
