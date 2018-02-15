<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\ArgumentsTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\ConfigObject;

class DirectiveDefinitionNode extends ConfigObject implements DefinitionNodeInterface
{

    use KindTrait;
    use LocationTrait;
    use DescriptionTrait;
    use NameTrait;
    use ArgumentsTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::DIRECTIVE_DEFINITION;

    /**
     * @var NameNode[]
     */
    protected $locations;

    /**
     * @return NameNode[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }
}
