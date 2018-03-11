<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\StringValueNode;
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
