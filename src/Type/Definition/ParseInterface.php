<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 07/02/2018
 * Time: 10.36
 */

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\ASTNodeInterface;

interface ParseInterface
{

    /**
     * @param $value
     * @return mixed
     */
    public function parseValue($value);

    /**
     * @param ASTNodeInterface $astNode
     * @param array            ...$args
     * @return mixed
     */
    public function parseLiteral(ASTNodeInterface $astNode, ...$args);
}
