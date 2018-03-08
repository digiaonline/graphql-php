<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Visitor\ParallelVisitor;
use Digia\GraphQL\Language\AST\Visitor\TypeInfoVisitor;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\TypeInfo;
use Digia\GraphQL\Validation\Rule\RuleInterface;

class Validator implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate(
        SchemaInterface $schema,
        DocumentNode $document,
        array $rules = [],
        ?TypeInfo $typeInfo = null
    ): array {
        $typeInfo = $typeInfo ?? new TypeInfo($schema);

        $context = new ValidationContext($schema, $document, $typeInfo);

        $visitors = array_map(function (RuleInterface $rule) use ($context) {
            return $rule->setContext($context);
        }, $rules ?? specifiedRules());

        $visitor = new TypeInfoVisitor($typeInfo, new ParallelVisitor($visitors));

        // Visit the whole document with each instance of all provided rules.
        $document->accept($visitor);

        return $context->getErrors();
    }
}
