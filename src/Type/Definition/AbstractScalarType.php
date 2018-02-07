<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\ASTNodeInterface;
use Digia\GraphQL\Language\AST\ASTNodeTrait;
use Digia\GraphQL\Language\AST\Node\ScalarTypeDefinitionNode;

abstract class AbstractScalarType implements LeafTypeInterface, NamedTypeInterface, InputTypeInterface, OutputTypeInterface, ParseInterface, SerializeInterface, TypeInterface
{

    use NameTrait;
    use DescriptionTrait;
    use ASTNodeTrait;
    use ConfigTrait;

    /**
     * @var ScalarTypeDefinitionNode
     */
    protected $astNode;

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValidValue($value): bool
    {
        return $this->parseValue($value) !== null;
    }

    /**
     * @param ASTNodeInterface $ast
     * @return bool
     */
    public function isValidLiteral($ast): bool
    {
        return $this->parseLiteral($ast) !== null;
    }
}
