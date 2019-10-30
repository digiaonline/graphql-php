<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;
use GraphQL\Contracts\TypeSystem\Common\NameAwareInterface;
use GraphQL\Contracts\TypeSystem\Common\FieldsAwareInterface as FieldsAwareContract;

interface FieldsAwareInterface extends NameAwareInterface, FieldsAwareContract
{
    /**
     * @return NodeInterface|null
     */
    public function getAstNode(): ?NodeInterface;

    /**
     * @return ObjectTypeExtensionNode[]
     */
    public function getExtensionAstNodes(): array;
}
