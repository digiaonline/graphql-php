<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\FileNotFoundException;
use Digia\GraphQL\Error\InvariantException;

/**
 * Class FileSourceBuilder
 * @package Digia\GraphQL\Language
 */
class FileSourceBuilder implements SourceBuilderInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * FileSourceBuilder constructor.
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @inheritdoc
     *
     * @throws FileNotFoundException
     * @throws InvariantException
     */
    public function build(): Source
    {
        if (!\file_exists($this->filePath) || !\is_readable($this->filePath)) {
            throw new FileNotFoundException(sprintf('The file %s cannot be found or is not readable', $this->filePath));
        }

        return new Source(\file_get_contents($this->filePath));
    }
}
