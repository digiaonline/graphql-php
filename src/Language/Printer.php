<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\LanguageException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\BooleanValueNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FloatValueNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\ListTypeNode;
use Digia\GraphQL\Language\Node\ListValueNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NonNullTypeNode;
use Digia\GraphQL\Language\Node\NullValueNode;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\ObjectValueNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Language\Node\StringValueNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use function Digia\GraphQL\Util\toString;

class Printer implements PrinterInterface
{
    /**
     * @inheritdoc
     * @throws LanguageException
     */
    public function print(NodeInterface $node): string
    {
        $printMethod = 'print' . $node->getKind();

        if (\method_exists($this, $printMethod)) {
            return $this->{$printMethod}($node);
        }

        throw new LanguageException(\sprintf('Invalid AST Node: %s.', toString($node)));
    }

    /**
     * @param NameNode $node
     * @return string
     */
    protected function printName(NameNode $node): string
    {
        return $node->getValue();
    }

    /**
     * @param VariableNode $node
     * @return string
     */
    protected function printVariable(VariableNode $node): string
    {
        return '$' . $node->getName();
    }

    // Document

    /**
     * @param DocumentNode $node
     * @return string
     */
    protected function printDocument(DocumentNode $node): string
    {
        return \implode("\n\n", $node->getDefinitions()) . "\n";
    }

    /**
     * @param OperationDefinitionNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printOperationDefinition(OperationDefinitionNode $node): string
    {
        $operation            = $node->getOperation();
        $name                 = $this->one($node->getName());
        $variablesDefinitions = $this->many($node->getVariableDefinitions());
        $directives           = $this->many($node->getDirectives());
        $selectionSet         = $this->one($node->getSelectionSet());

        // Anonymous queries with no directives or variable definitions can use
        // the query short form.
        return null === $name && empty($directives) && empty($variablesDefinitions) && $operation === 'query'
            ? $selectionSet
            : \implode(' ', [
                $operation,
                $name . wrap('(', \implode(', ', $variablesDefinitions), ')'),
                \implode(' ', $directives),
                $selectionSet,
            ]);
    }

    /**
     * @param VariableDefinitionNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printVariableDefinition(VariableDefinitionNode $node): string
    {
        $variable     = $this->one($node->getVariable());
        $type         = $this->one($node->getType());
        $defaultValue = $this->one($node->getDefaultValue());

        return $variable . ': ' . $type . wrap(' = ', $defaultValue);
    }

    /**
     * @param SelectionSetNode $node
     * @return string
     */
    protected function printSelectionSet(SelectionSetNode $node): string
    {
        return block($this->many($node->getSelections()));
    }

    /**
     * @param FieldNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printField(FieldNode $node): string
    {
        $alias        = $this->one($node->getAlias());
        $name         = $this->one($node->getName());
        $arguments    = $this->many($node->getArguments());
        $directives   = $this->many($node->getDirectives());
        $selectionSet = $this->one($node->getSelectionSet());

        return \implode(' ', [
            wrap('', $alias, ': ') . $name . wrap('(', \implode(', ', $arguments), ')'),
            \implode(' ', $directives),
            $selectionSet,
        ]);
    }

    /**
     * @param ArgumentNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printArgument(ArgumentNode $node): string
    {
        $name  = $this->one($node->getName());
        $value = $this->one($node->getValue());

        return $name . ': ' . $value;
    }

    // Fragments

    /**
     * @param FragmentSpreadNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printFragmentSpread(FragmentSpreadNode $node): string
    {
        $name       = $this->one($node->getName());
        $directives = $this->many($node->getDirectives());

        return '...' . $name . wrap(' ', \implode(' ', $directives));
    }

    /**
     * @param InlineFragmentNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printInlineFragment(InlineFragmentNode $node): string
    {
        $typeCondition = $this->one($node->getTypeCondition());
        $directives    = $this->many($node->getDirectives());
        $selectionSet  = $this->one($node->getSelectionSet());

        return \implode(' ', [
            '...', wrap('on ', $typeCondition),
            \implode(' ', $directives),
            $selectionSet
        ]);
    }

    /**
     * @param FragmentDefinitionNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printFragmentDefinition(FragmentDefinitionNode $node): string
    {
        $name                = $this->one($node->getName());
        $typeCondition       = $this->one($node->getTypeCondition());
        $variableDefinitions = $this->many($node->getVariableDefinitions());
        $directives          = $this->many($node->getDirectives());
        $selectionSet        = $this->one($node->getSelectionSet());

        // Note: fragment variable definitions are experimental and may be changed
        // or removed in the future.
        return \implode(' ', [
            'fragment ' . $name . wrap('(', \implode(', ', $variableDefinitions), ')'),
            'on ' . $typeCondition . ' ' . \implode(' ', $directives),
            $selectionSet
        ]);
    }

    // Value

    /**
     * @param IntValueNode $node
     * @return string
     */
    protected function printIntValue(IntValueNode $node): string
    {
        return $node->getValue();
    }

    /**
     * @param FloatValueNode $node
     * @return string
     */
    protected function printFloatValue(FloatValueNode $node): string
    {
        return $node->getValue();
    }

    /**
     * @param StringValueNode $node
     * @return string
     */
    protected function printStringValue(StringValueNode $node): string
    {
        $value = $node->getValue();

        return $node->isBlock()
            ? printBlockString($value, false)
            : \json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param BooleanValueNode $node
     * @return string
     */
    protected function printBooleanValue(BooleanValueNode $node): string
    {
        return $node->getValue() ? 'true' : 'false';
    }

    /**
     * @param NullValueNode $node
     * @return string
     */
    protected function printNullValue(NullValueNode $node): string
    {
        return 'null';
    }

    /**
     * @param EnumValueNode $node
     * @return string
     */
    protected function printEnumValue(EnumValueNode $node): string
    {
        return $node->getValue();
    }

    /**
     * @param ListValueNode $node
     * @return string
     */
    protected function printListValue(ListValueNode $node): string
    {
        $values = $this->many($node->getValues());
        return wrap('[', \implode(', ', $values), ']');
    }

    /**
     * @param ObjectValueNode $node
     * @return string
     */
    protected function printObjectValue(ObjectValueNode $node): string
    {
        $fields = $this->many($node->getFields());
        return wrap('{', \implode(', ', $fields), '}');
    }

    /**
     * @param ObjectFieldNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printObjectField(ObjectFieldNode $node): string
    {
        $name  = $this->one($node->getName());
        $value = $this->one($node->getValue());

        return $name . ': ' . $value;
    }

    // Directive

    /**
     * @param DirectiveNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printDirective(DirectiveNode $node): string
    {
        $name      = $this->one($node->getName());
        $arguments = $this->many($node->getArguments());

        return '@' . $name . wrap('(', \implode(', ', $arguments), ')');
    }

    // Type

    /**
     * @param NamedTypeNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printNamedType(NamedTypeNode $node): string
    {
        return $this->one($node->getName());
    }

    /**
     * @param ListTypeNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printListType(ListTypeNode $node): string
    {
        return wrap('[', $this->one($node->getType()), ']');
    }

    /**
     * @param NonNullTypeNode $node
     * @return string
     * @throws LanguageException
     */
    protected function printNonNullType(NonNullTypeNode $node): string
    {
        return $this->one($node->getType()) . '!';
    }

    /**
     * @param NodeInterface|null $node
     * @return string
     * @throws LanguageException
     */
    protected function one(?NodeInterface $node): string
    {
        return null !== $node ? $this->print($node) : '';
    }

    /**
     * @param array $nodes
     * @return array
     */
    protected function many(array $nodes): array
    {
        return \array_map(function ($node) {
            return $this->print($node);
        }, $nodes);
    }
}
