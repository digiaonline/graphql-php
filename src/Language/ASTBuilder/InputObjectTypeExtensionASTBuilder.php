<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\KeywordEnum;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class InputObjectTypeExtensionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::INPUT_OBJECT_TYPE_EXTENSION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $this->expectKeyword($lexer, KeywordEnum::EXTEND);
        $this->expectKeyword($lexer, KeywordEnum::INPUT);

        $name       = $this->buildAST(ASTKindEnum::NAME, $lexer);
        $directives = $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer, ['isConst' => true]);
        $fields     = $this->buildAST(ASTKindEnum::INPUT_FIELDS_DEFINITION, $lexer);

        if (count($directives) === 0 && count($fields) === 0) {
            throw $this->unexpected($lexer);
        }

        return [
            'kind'       => NodeKindEnum::INPUT_OBJECT_TYPE_EXTENSION,
            'name'       => $name,
            'directives' => $directives,
            'fields'     => $fields,
            'loc'        => $this->buildLocation($lexer, $start),
        ];
    }
}
