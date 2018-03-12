<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Visitor\ParallelVisitor;
use Digia\GraphQL\Language\Visitor\TypeInfoVisitor;
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
     * @var RuleInterface[]
     */
    protected $rules;

    /**
     * Validator constructor.
     * @param ContextBuilderInterface $contextBuilder
     * @param RuleInterface[]         $rules
     */
    public function __construct(ContextBuilderInterface $contextBuilder, array $rules)
    {
        $this->contextBuilder = $contextBuilder;
        $this->rules          = $rules;
    }

    /**
     * @inheritdoc
     */
    public function validate(
        SchemaInterface $schema,
        DocumentNode $document,
        ?array $rules = null,
        ?TypeInfo $typeInfo = null
    ): array {
        $typeInfo = $typeInfo ?? new TypeInfo($schema);
        $rules    = $rules ?? $this->rules;

        $context = $this->contextBuilder->build($schema, $document, $typeInfo);

        $visitors = array_map(function (RuleInterface $rule) use ($context) {
            return $rule->setValidationContext($context);
        }, $rules);

        $visitor = new TypeInfoVisitor($typeInfo, new ParallelVisitor($visitors));

        // Visit the whole document with each instance of all provided rules.
        $document->acceptVisitor($visitor);

        return $context->getErrors();
    }
}
