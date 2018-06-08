<?php

namespace Digia\GraphQL\Language\Node;

interface NameAwareInterface
{
    /**
     * @return NameNode|null
     */
    public function getName(): ?NameNode;

    /**
     * @return string|null
     */
    public function getNameValue(): ?string;

    /**
     * @return array|null
     */
    public function getNameAST(): ?array;

    /**
     * @param NameNode|null $name
     * @return $this
     */
    public function setName(?NameNode $name);

    /**
     * @inheritdoc
     */
    public function __toString(): string;
}
