<?php

namespace Digia\GraphQL\Test\Unit\Schema\Resolver;

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
        ], $registry->lookup('Query', 'human')(null, ['id' => '1000']));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'R2-D2',
        ], $registry->lookup('Query', 'droid')(null, ['id' => '2001']));
    }

    public function testResolverClassMap()
    {
        $registry = new ResolverRegistry([
            'Query' => QueryResolver::class,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->lookup('Query', 'human')(null, ['id' => '1000']));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'R2-D2',
        ], $registry->lookup('Query', 'droid')(null, ['id' => '2001']));
    }

    public function testRegisterResolver()
    {
        $registry = new ResolverRegistry();

        $registry->register('Query', new QueryResolver());

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->lookup('Query', 'human')(null, ['id' => '1000']));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'R2-D2',
        ], $registry->lookup('Query', 'droid')(null, ['id' => '2001']));
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
        ], $registry->lookup('Query', 'human')(null, ['id' => '1000']));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'R2-D2',
        ], $registry->lookup('Query', 'droid')(null, ['id' => '2001']));

        /** @noinspection PhpUndefinedMethodInspection */
        $registry->getResolver('Query')->addResolver('hero', function ($_, $args) {
            return getHero($args['episode'] ?? null);
        });

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->lookup('Query', 'hero')(null, ['episode' => 'EMPIRE']));
    }
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
}
