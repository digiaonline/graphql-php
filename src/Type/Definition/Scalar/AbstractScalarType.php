<?php

namespace Digia\GraphQL\Type\Definition\Scalar;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\ScalarTypeDefinitionNode;
use Digia\GraphQL\Type\Behavior\NameTrait;
use Digia\GraphQL\Type\Contract\InputTypeInterface;
use Digia\GraphQL\Type\Contract\LeafTypeInterface;
use Digia\GraphQL\Type\Contract\NamedTypeInterface;
use Digia\GraphQL\Type\Contract\OutputTypeInterface;
use Digia\GraphQL\Type\Contract\TransformInterface;
use Digia\GraphQL\Type\Contract\TypeInterface;

/**
 * Class AbstractScalarType
 *
 * @package Digia\GraphQL\Type\Definition
 * @property ScalarTypeDefinitionNode $astNode
 * @codeCoverageIgnore
 */
abstract class AbstractScalarType implements TypeInterface, LeafTypeInterface, NamedTypeInterface, InputTypeInterface, OutputTypeInterface, TransformInterface
{

    use NameTrait;
    use DescriptionTrait;
    use NodeTrait;
    use ConfigTrait;

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValidValue($value): bool
    {
        return $this->parseValue($value) !== null;
    }

    /**
     * @param NodeInterface $ast
     * @return bool
     */
    public function isValidLiteral($ast): bool
    {
        return $this->parseLiteral($ast) !== null;
    }
}
