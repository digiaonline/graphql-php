<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class OperationTypeDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{
    use TypeTrait;

    /**
     * @var string
     */
    protected $operation;

    /**
     * OperationTypeDefinitionNode constructor.
     *
     * @param string            $operation
     * @param TypeNodeInterface $type
     * @param Location|null     $location
     */
    public function __construct(string $operation, TypeNodeInterface $type, ?Location $location)
    {
        parent::__construct(NodeKindEnum::OPERATION_TYPE_DEFINITION, $location);

        $this->operation = $operation;
        $this->type      = $type;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'      => $this->kind,
            'operation' => $this->operation,
            'type'      => $this->getTypeAST(),
            'loc'       => $this->getLocationAST(),
        ];
    }
}
