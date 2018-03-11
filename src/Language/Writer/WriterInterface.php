<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\PrinterInterface;

interface WriterInterface
{
    /**
     * @param NodeInterface $node
     * @return string
     */
    public function write(NodeInterface $node): string;

    /**
     * @param NodeInterface $node
     * @return bool
     */
    public function supportsWriter(NodeInterface $node): bool;

    /**
     * @param PrinterInterface $printer
     * @return $this
     */
    public function setPrinter(PrinterInterface $printer);
}
