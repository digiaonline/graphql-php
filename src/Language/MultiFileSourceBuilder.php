<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\FileNotFoundException;
use Digia\GraphQL\Error\InvariantException;

/**
 * Class MultiFileSourceBuilder
 * @package Digia\GraphQL\Language
 */
class MultiFileSourceBuilder implements SourceBuilderInterface
{

    /**
     * @var string[]
     */
    private $filePaths;

    /**
     * MultiFileSourceBuilder constructor.
     * @param string[] $filePaths
     */
    public function __construct(array $filePaths)
    {
        $this->filePaths = $filePaths;
    }

    /**
     * @inheritdoc
     *
     * @throws FileNotFoundException
     * @throws InvariantException
     */
    public function build(): Source
    {
        $combinedSource = '';

        foreach ($this->filePaths as $filePath) {
            if (!\file_exists($filePath) || !\is_readable($filePath)) {
                throw new FileNotFoundException(sprintf('The file %s cannot be found or is not readable', $filePath));
            }

            $combinedSource .= \file_get_contents($filePath);
        }

        return new Source($combinedSource);
    }
}
