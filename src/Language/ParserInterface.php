<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\TypeNodeInterface;

interface ParserInterface
{

    /**
     * Given a GraphQL source, parses it into a Document.
     *
     * @param Source|string $source
     * @param array         $options
     * @return DocumentNode
     */
    public function parse($source, array $options = []): DocumentNode;
}
