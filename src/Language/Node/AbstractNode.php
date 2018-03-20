<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Config\ConfigAwareInterface;
use Digia\GraphQL\Config\ConfigAwareTrait;
use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Language\Visitor\AcceptsVisitorsInterface;
use Digia\GraphQL\Language\Visitor\AcceptsVisitorsTrait;
use Digia\GraphQL\Util\ArrayToJsonTrait;
use Digia\GraphQL\Util\SerializationInterface;

abstract class AbstractNode implements SerializationInterface, AcceptsVisitorsInterface, ConfigAwareInterface
{
    use ConfigAwareTrait;
    use AcceptsVisitorsTrait;
    use ArrayToJsonTrait;

    /**
     * @var string
     */
    protected $kind;

    /**
     * @var Location|null
     */
    protected $location;

    /**
     * @return string
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * @return Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @return array|null
     */
    public function getLocationAsArray(): ?array
    {
        return null !== $this->location ? $this->location->toArray() : null;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        // TODO: Remove this method when every node implement its own toArray-method.

        return [
            'kind' => $this->kind,
            'loc'  => $this->getLocationAsArray(),
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJSON();
    }
}
