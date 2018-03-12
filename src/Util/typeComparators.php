<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\SchemaInterface;

/**
 * Provided two composite types, determine if they "overlap". Two composite
 * types overlap when the Sets of possible concrete types for each intersect.
 *
 * This is often used to determine if a fragment of a given type could possibly
 * be visited in a context of another type.
 *
 * @param SchemaInterface        $schema
 * @param TypeInterface $typeA
 * @param TypeInterface $typeB
 * @return bool
 */
function doTypesOverlap(SchemaInterface $schema, TypeInterface $typeA, TypeInterface $typeB): bool
{
    // Equivalent types overlap
    if ($typeA === $typeB) {
        return true;
    }

    if ($typeA instanceof AbstractTypeInterface) {
        if ($typeB instanceof AbstractTypeInterface) {
            // If both types are abstract, then determine if there is any intersection
            // between possible concrete types of each.
            return arraySome($schema->getPossibleTypes($typeA), function (TypeInterface $type) use ($schema, $typeB) {
                return $schema->isPossibleType($typeB, $type);
            });
        }

        // Determine if the latter type is a possible concrete type of the former.
        /** @noinspection PhpParamsInspection */
        return $schema->isPossibleType($typeA, $typeB);
    }

    if ($typeB instanceof AbstractTypeInterface) {
        // Determine if the former type is a possible concrete type of the latter.
        /** @noinspection PhpParamsInspection */
        return $schema->isPossibleType($typeB, $typeA);
    }

    // Otherwise the types do not overlap.
    return false;
}
