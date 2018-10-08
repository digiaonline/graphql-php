<?php

namespace Digia\GraphQL\Test\Unit\Schema\Resolver;

use Digia\GraphQL\Execution\ResolveInfo;
use Digia\GraphQL\Schema\Resolver\AbstractResolver;
use Digia\GraphQL\Schema\Resolver\ResolverRegistry;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Test\Functional\getDroid;
use function Digia\GraphQL\Test\Functional\getHero;
use function Digia\GraphQL\Test\Functional\getHuman;

class ResolverRegistryTest extends TestCase
{
    public function testArrayResolver()
    {
        $registry = new ResolverRegistry([
            'Query' => [
                'human' => function ($_, $args) {
                    return getHuman($args['id']);
                },
                'droid' => function ($_, $args) {
                    return getDroid($args['id']);
                },
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->getFieldResolver('Query', 'human')(null, ['id' => '1000']));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'R2-D2',
        ], $registry->getFieldResolver('Query', 'droid')(null, ['id' => '2001']));
    }

    public function testResolverClassMap()
    {
        $registry = new ResolverRegistry([
            'Query' => QueryResolver::class,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->getFieldResolver('Query', 'human')(null, ['id' => '1000']));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'R2-D2',
        ], $registry->getFieldResolver('Query', 'droid')(null, ['id' => '2001']));
    }

    public function testRegisterResolver()
    {
        $registry = new ResolverRegistry();

        $registry->register('Query', new QueryResolver());

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->getFieldResolver('Query', 'human')(null, ['id' => '1000']));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'R2-D2',
        ], $registry->getFieldResolver('Query', 'droid')(null, ['id' => '2001']));
    }

    public function testExtendExistingResolver()
    {
        $registry = new ResolverRegistry([
            'Query' => [
                'human' => function ($_, $args) {
                    return getHuman($args['id']);
                },
                'droid' => function ($_, $args) {
                    return getDroid($args['id']);
                },
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->getFieldResolver('Query', 'human')(null, ['id' => '1000']));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'R2-D2',
        ], $registry->getFieldResolver('Query', 'droid')(null, ['id' => '2001']));

        /** @noinspection PhpUndefinedMethodInspection */
        $registry->getResolver('Query')->addResolver('hero', function ($_, $args) {
            return getHero($args['episode'] ?? null);
        });

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->getFieldResolver('Query', 'hero')(null, ['episode' => 'EMPIRE']));
    }

    public function testBeforeResolve()
    {
        $registry = new ResolverRegistry([
            'Query' => QueryResolverWithBeforeResolve::class,
        ]);

        $this->expectException(BeforeResolveException::class);
        $registry->getFieldResolver('Query', 'human')(null, ['id' => '1000']);
    }

    public function testAfterResolve()
    {
        $registry = new ResolverRegistry([
            'Query' => QueryResolverWithAfterResolve::class,
        ]);

        $this->expectException(AfterResolveException::class);
        $registry->getFieldResolver('Query', 'human')(null, ['id' => '1000']);
    }
}

class BeforeResolveException extends \Exception
{
}

class AfterResolveException extends \Exception
{
}

class QueryResolver extends AbstractResolver
{
    public function resolveHero($_, $args)
    {
        return getHero($args['episode'] ?? null);
    }

    public function resolveHuman($_, $args)
    {
        return getHuman($args['id']);
    }

    public function resolveDroid($_, $args)
    {
        return getDroid($args['id']);
    }

    public function resolveType($rootValue, $contextValues, ResolveInfo $info): ?callable
    {
        return null;
    }
}

class QueryResolverWithBeforeResolve extends QueryResolver
{
    public function beforeResolve(
        callable $resolveCallback,
        $rootValue,
        array $args,
        $contextValues = null,
        ?ResolveInfo $info = null
    ) {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new BeforeResolveException();
    }
}

class QueryResolverWithAfterResolve extends QueryResolver
{
    public function afterResolve(
        $result,
        $rootValue,
        array $args,
        $contextValues = null,
        ?ResolveInfo $info = null
    ) {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new AfterResolveException();
    }
}
