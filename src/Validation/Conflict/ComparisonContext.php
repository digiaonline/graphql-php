<?php

namespace Digia\GraphQL\Validation\Conflict;

use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\NodeInterface;

class ComparisonContext
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
     * @var Conflict[]
     */
    protected $conflicts = [];

    /**
     * @param FieldContext $field
     *
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
     *
     * @return $this
     */
    public function registerFragment(NodeInterface $fragment)
    {
        $this->fragmentNames[] = $fragment->getNameValue();

        return $this;
    }

    /**
     * @param Conflict $conflict
     *
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
     * @return Conflict[]
     */
    public function getConflicts(): array
    {
        return $this->conflicts;
    }
}
