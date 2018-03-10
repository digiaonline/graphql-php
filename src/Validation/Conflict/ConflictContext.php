<?php

namespace Digia\GraphQL\Validation\Conflict;

use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\AST\Node\InlineFragmentNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

class ConflictContext
{
    /**
     * @var array
     */
    protected $fieldMap = [];

    /**
     * @var array
     */
    protected $fragmentNames = [];

    /**
     * @var array|Conflict[]
     */
    protected $conflicts = [];

    /**
     * @param FieldContext $field
     * @return $this
     */
    public function registerField(FieldContext $field)
    {
        $responseName = $field->getNode()->getAliasOrNameValue();

        if (!isset($this->fieldMap[$responseName])) {
            $this->fieldMap[$responseName] = [];
        }

        $this->fieldMap[$responseName][] = $field;

        return $this;
    }

    /**
     * @param NodeInterface|FragmentSpreadNode|FragmentDefinitionNode $fragment
     * @return $this
     */
    public function registerFragment(NodeInterface $fragment)
    {
        $this->fragmentNames[$fragment->getNameValue()] = true;

        return $this;
    }

    /**
     * @param Conflict $conflict
     * @return $this
     */
    public function reportConflict(Conflict $conflict)
    {
        $this->conflicts[] = $conflict;

        return $this;
    }

    /**
     * @return array
     */
    public function getFieldMap(): array
    {
        return $this->fieldMap;
    }

    /**
     * @return array
     */
    public function getFragmentNames(): array
    {
        return $this->fragmentNames;
    }

    /**
     * @return bool
     */
    public function hasConflicts(): bool
    {
        return !empty($this->conflicts);
    }

    /**
     * @return array|Conflict[]
     */
    public function getConflicts()
    {
        return $this->conflicts;
    }
}
