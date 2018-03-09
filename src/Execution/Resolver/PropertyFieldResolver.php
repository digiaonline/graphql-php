<?php

namespace Digia\GraphQL\Execution\Resolver;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Execution\ExecutionEnvironment;

class PropertyFieldResolver implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(ExecutionEnvironment $environment)
    {
        $value     = $environment->getValue();
        $fieldName = $environment->getInfo()->getFieldName();

        if (\is_array($value) || $value instanceof \ArrayAccess) {
            return $value[$fieldName] ?? null;
        }

        if (\is_object($value)) {
            $getter = 'get' . ucfirst($fieldName);

            if (method_exists($value, $getter)) {
                return $value->{$getter}();
            }

            if (property_exists($value, $fieldName)) {
                return $value->{$fieldName};
            }
        }

        throw new ExecutionException(sprintf('Could not resolve value for field "%s".', $fieldName));
    }
}
