<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\NodeInterface;

class NameHelper
{
    /**
     * Upholds the spec rules about naming.
     *
     * @param string $name
     * @return string
     * @throws InvariantException
     * @throws ValidationException
     */
    public function assertInvalid(string $name): string
    {
        $error = $this->isValidError($name);

        if ($error) {
            throw $error;
        }

        return $name;
    }

    /**
     * Returns an Error if a name is invalid.
     *
     * @param string     $name
     * @param mixed|null $node
     * @return ValidationException
     * @throws InvariantException
     */
    public function isValidError(string $name, $node = null): ?ValidationException
    {
        invariant(\is_string($name), 'Expected string');

        if (\strlen($name) > 1 && $name{0} === '_' && $name{1} === '_') {
            return new ValidationException(
                sprintf('Name "%s" must not begin with "__", which is reserved by GraphQL introspection.', $name),
                $node instanceof NodeInterface ? [$node] : null
            );
        }

        if (preg_match("/^[_a-zA-Z][_a-zA-Z0-9]*$/", $name) === 0) {
            return new ValidationException(
                sprintf('Names must match /^[_a-zA-Z][_a-zA-Z0-9]*$/ but "%s" does not.', $name),
                $node instanceof NodeInterface ? [$node] : null
            );
        }

        return null;
    }
}
