<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\LanguageException;
use Digia\GraphQL\Language\LexerInterface;

class ASTDirector implements ASTDirectorInterface
{
    /**
     * @var ASTBuilderInterface[]
     */
    protected $builders;

    /**
     * ASTBuilder constructor.
     *
     * @param ASTBuilderInterface[] $builders
     */
    public function __construct($builders)
    {
        foreach ($builders as $builder) {
            $builder->setDirector($this);
        }

        $this->builders = $builders;
    }

    /**
     * @inheritdoc
     */
    public function build(string $kind, LexerInterface $lexer, array $params = []): ?array
    {
        $builder = $this->getBuilder($kind);

        if ($builder !== null) {
            return $builder->build($lexer, $params);
        }

        throw new LanguageException(sprintf('AST of kind "%s" not supported.', $kind));
    }

    /**
     * @param string $kind
     * @return ASTBuilderInterface|null
     */
    protected function getBuilder(string $kind): ?ASTBuilderInterface
    {
        foreach ($this->builders as $builder) {
            if ($builder instanceof ASTBuilderInterface && $builder->supportsBuilder($kind)) {
                return $builder;
            }
        }

        return null;
    }
}
