<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\ASTNodeTrait;
use Digia\GraphQL\Language\AST\Node\EnumValueDefinitionNode;

/**
 * Class EnumValue
 *
 * @package Digia\GraphQL\Type\Definition\Enum
 * @property EnumValueDefinitionNode $astNode
 */
class EnumValue
{

    use TypeTrait;
    use NameTrait;
    use DescriptionTrait;
    use IsDeprecatedTrait;
    use DeprecationReasonTrait;
    use ValueTrait;
    use ASTNodeTrait;
    use ConfigTrait;

    /**
     * @param bool $isDeprecated
     * @throws \TypeError
     */
    public function setIsDeprecated(bool $isDeprecated): void
    {
        throw new \TypeError(sprintf(
            '%s.%s should provide "deprecationReason" instead of "isDeprecated".',
            $this->getType(),
            $this->getName()
        ));
    }
}
