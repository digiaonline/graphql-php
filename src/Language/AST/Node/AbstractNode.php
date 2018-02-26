<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Language\AST\Visitor\AcceptVisitorInterface;
use Digia\GraphQL\Language\AST\Visitor\AcceptVisitorTrait;
use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Util\SerializationInterface;
use function Digia\GraphQL\Util\jsonEncode;

abstract class AbstractNode extends ConfigObject implements SerializationInterface, AcceptVisitorInterface
{

    use AcceptVisitorTrait;

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
    public function toJSON(): string
    {
        return jsonEncode($this->toArray());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJSON();
    }
}
