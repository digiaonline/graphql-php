<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\FieldsTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\InterfacesTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Contract\TypeExtensionNodeInterface;
use Digia\GraphQL\ConfigObject;

class ObjectTypeExtensionNode extends ConfigObject implements TypeExtensionNodeInterface
{

    use KindTrait;
    use LocationTrait;
    use NameTrait;
    use InterfacesTrait;
    use DirectivesTrait;
    use FieldsTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::OBJECT_TYPE_EXTENSION;
}
