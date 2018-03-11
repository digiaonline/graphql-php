<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\AST\Node\IntValueNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use function Digia\GraphQL\Util\jsonEncode;

class StringValueWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|StringValueNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $value = $node->getValue();

        // TODO: Support printing block strings.
        return jsonEncode($value);
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof StringValueNode;
    }
}
