<?php

namespace Digia\GraphQL\Schema\Validation\Rule;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Schema\Validation\SchemaValidationException;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Util\NameHelper;
use function Digia\GraphQL\Type\isInputType;
use function Digia\GraphQL\Util\toString;

class DirectivesRule extends AbstractRule
{
    /**
     * @inheritdoc
     * @throws InvariantException
     */
    public function evaluate(): void
    {
        $directives = $this->context->getSchema()->getDirectives();

        foreach ($directives as $directive) {
            if (!($directive instanceof Directive)) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf('Expected directive but got: %s.', toString($directive)),
                        $directive instanceof ASTNodeAwareInterface ? [$directive->getAstNode()] : null
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

        /** @var DirectiveNode|null $directiveNode */
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
     */
    protected function validateName($node): void
    {
        // Ensure names are valid, however introspection types opt out.
        $error = NameHelper::isValidError($node->getName(), $node);

        if (null !== $error) {
            $this->context->reportError($error);
        }
    }
}
