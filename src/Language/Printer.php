<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\LanguageException;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Writer\WriterInterface;

class Printer implements PrinterInterface
{
    /**
     * @var array|WriterInterface[]
     */
    protected $writers;

    /**
     * Printer constructor.
     * @param array|WriterInterface[] $writers
     */
    public function __construct(array $writers)
    {
        foreach ($writers as $writer) {
            $writer->setPrinter($this);
        }

        $this->writers = $writers;
    }

    /**
     * @inheritdoc
     * @throws LanguageException
     */
    public function print(NodeInterface $node): string
    {
        if (($writer = $this->getWriter($node)) !== null) {
            return $writer->write($node);
        }

        throw new LanguageException(sprintf('Invalid AST Node: %s', (string)$node));
    }

    /**
     * @param NodeInterface $node
     * @return WriterInterface|null
     */
    protected function getWriter(NodeInterface $node): ?WriterInterface
    {
        foreach ($this->writers as $writer) {
            if ($writer instanceof WriterInterface && $writer->supportsWriter($node)) {
                return $writer;
            }
        }

        return null;
    }
}
