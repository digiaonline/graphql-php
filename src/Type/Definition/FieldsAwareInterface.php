<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;

interface FieldsAwareInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return NodeInterface|null
     */
    public function getAstNode(): ?NodeInterface;

    /**
     * @return ObjectTypeExtensionNode[]
     */
    public function getExtensionAstNodes(): array;

    /**
     * @param string $fieldName
     * @return Field|null
     */
    public function getField(string $fieldName): ?Field;

    /**
     * @return Field[]
     */
    public function getFields(): array;
}
