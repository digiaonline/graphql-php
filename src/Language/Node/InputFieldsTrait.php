<?php

namespace Digia\GraphQL\Language\Node;

trait InputFieldsTrait
{
    /**
     * @var InputValueDefinitionNode[]
     */
    protected $fields;

    /**
     * @return bool
     */
    public function hasFields(): bool
    {
        return !empty($this->fields);
    }

    /**
     * @return InputValueDefinitionNode[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getFieldsAST(): array
    {
        return \array_map(function (InputValueDefinitionNode $node) {
            return $node->toAST();
        }, $this->fields);
    }

    /**
     * @param array|InputValueDefinitionNode[] $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }
}
