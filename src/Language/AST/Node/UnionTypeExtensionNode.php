<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\TypesTrait;
use Digia\GraphQL\Language\AST\Node\TypeExtensionNodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class UnionTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface
{

    use NameTrait;
    use DirectivesTrait;
    use TypesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::UNION_TYPE_EXTENSION;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'       => $this->kind,
            'name'       => $this->getNameAsArray(),
            'directives' => $this->getDirectivesAsArray(),
            'types'      => $this->getTypesAsArray(),
            'loc'        => $this->getLocationAsArray(),
        ];
    }
}
