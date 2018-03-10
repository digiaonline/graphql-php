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
     * @var ContextBuilderInterface
     */
    protected $contextBuilder;

    /**
     * Validator constructor.
     * @param ContextBuilderInterface $contextBuilder
     */
    public function __construct(ContextBuilderInterface $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
    }

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

        $context = $this->contextBuilder->build($schema, $document, $typeInfo);

        $visitors = array_map(function (RuleInterface $rule) use ($context) {
            return $rule->setValidationContext($context);
        }, $rules ?? specifiedRules());

        $visitor = new TypeInfoVisitor($typeInfo, new ParallelVisitor($visitors));

        // Visit the whole document with each instance of all provided rules.
        $document->accept($visitor);

        return $context->getErrors();
    }
}
