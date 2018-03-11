<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\PrinterInterface;

abstract class AbstractWriter implements WriterInterface
{
    /**
     * @var PrinterInterface
     */
    protected $printer;

    /**
     * @inheritdoc
     */
    public function setPrinter(PrinterInterface $printer)
    {
        $this->printer = $printer;
        return $this;
    }

    /**
     * @param NodeInterface|null $node
     * @return string
     */
    protected function printNode(?NodeInterface $node): string
    {
        return null !== $node ? $this->printer->print($node) : '';
    }

    /**
     * @param array $nodes
     * @return array
     */
    protected function printNodes(array $nodes): array
    {
        return array_map(function ($node) {
            return $this->printer->print($node);
        }, $nodes);
    }
}
