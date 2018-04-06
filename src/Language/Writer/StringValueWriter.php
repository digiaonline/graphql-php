<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\StringValueNode;

class StringValueWriter extends AbstractWriter
{

    /**
     * @param NodeInterface|StringValueNode $node
     *
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $value = $node->getValue();

        // TODO: Support printing block strings.

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof StringValueNode;
    }
}
