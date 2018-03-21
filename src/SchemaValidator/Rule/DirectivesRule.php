<?php

namespace Digia\GraphQL\SchemaValidator\Rule;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use Digia\GraphQL\Util\NameValidator;
use function Digia\GraphQL\Type\isInputType;

class DirectivesRule extends AbstractRule
{
    /**
     * @var NameValidator
     */
    protected $nameValidator;

    /**
     * DirectivesRule constructor.
     * @param NameValidator $nameValidator
     */
    public function __construct(NameValidator $nameValidator)
    {
        $this->nameValidator = $nameValidator;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(): void
    {
        $directives = $this->context->getSchema()->getDirectives();

        foreach ($directives as $directive) {
            if (!($directive instanceof DirectiveInterface)) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf(
                            'Expected directive but got: %s.',
                            $directive instanceof NodeAwareInterface ? $directive->getAstNode() : $directive
                        )
                    )
                );

                return;
            }

            // Ensure they are named correctly.
            $this->validateName($directive);

            // TODO: Ensure proper locations.

            // Ensure the arguments are valid.
            $argumentNames = [];

            foreach ($directive->getArguments() as $argument) {
                $argumentName = $argument->getName();

                // Ensure they are named correctly.
                $this->validateName($argument);

                // Ensure they are unique per directive.
                if (isset($argumentNames[$argumentName])) {
                    $this->context->reportError(
                        new SchemaValidationException(
                            \sprintf(
                                'Argument @%s(%s:) can only be defined once.',
                                $directive->getName(),
                                $argumentName
                            ),
                            $this->getAllDirectiveArgumentNodes($directive, $argumentName)
                        )
                    );

                    continue;
                }

                $argumentNames[$argumentName] = true;

                // Ensure the type is an input type.
                if (!isInputType($argument->getType())) {
                    $this->context->reportError(
                        new SchemaValidationException(
                            \sprintf(
                                'The type of @%s(%s:) must be Input Type but got: %s.',
                                $directive->getName(),
                                $argumentName,
                                (string)$argument->getType()
                            ),
                            $this->getAllDirectiveArgumentNodes($directive, $argumentName)
                        )
                    );
                }
            }
        }
    }

    /**
     * @param Directive $directive
     * @param string    $argumentName
     * @return array
     */
    protected function getAllDirectiveArgumentNodes(Directive $directive, string $argumentName)
    {
        $nodes = [];

        /** @var DirectiveNode $directiveNode */
        $directiveNode = $directive->getAstNode();

        if (null !== $directiveNode) {
            foreach ($directiveNode->getArguments() as $node) {
                if ($node->getNameValue() === $argumentName) {
                    $nodes[] = $node;
                }
            }
        }

        return $nodes;
    }

    /**
     * @param mixed $node
     * @throws InvariantException
     */
    protected function validateName($node): void
    {
        // Ensure names are valid, however introspection types opt out.
        $error = $this->nameValidator->isValidNameError($node->getName(), $node);

        if (null !== $error) {
            $this->context->reportError($error);
        }
    }
}
