<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\Node\DocumentNode;

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
