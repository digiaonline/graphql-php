<?php

namespace Digia\GraphQL\Test\Unit\Schema\Resolver;

use Digia\GraphQL\Execution\ResolveInfo;
use Digia\GraphQL\Schema\Resolver\AbstractTypeResolver;
use Digia\GraphQL\Schema\Resolver\AbstractFieldResolver;
use Digia\GraphQL\Schema\Resolver\ResolverMiddlewareInterface;
use Digia\GraphQL\Schema\Resolver\ResolverRegistry;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Test\Functional\getDroid;
use function Digia\GraphQL\Test\Functional\getHero;
use function Digia\GraphQL\Test\Functional\getHuman;

class ResolverRegistryTest extends TestCase
{
    public function testResolverMap()
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

    public function testTypeResolver()
    {
        $registry = new ResolverRegistry([
            'Query' => new QueryResolver(),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->getFieldResolver('Query', 'human')(null, ['id' => '1000']));
    }

    public function testFieldResolver()
    {
        $registry = new ResolverRegistry([
            'Query' => [
                'human' => new HumanResolver(),
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->getFieldResolver('Query', 'human')(null, ['id' => '1000']));
    }

    public function testRegisterResolver()
    {
        $registry = new ResolverRegistry();

        $registry->register('Query', new QueryResolver());

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertArraySubset([
            'name' => 'Luke Skywalker',
        ], $registry->getFieldResolver('Query', 'human')(null, ['id' => '1000']));
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

    public function testMiddleware()
    {
        $messages = [];

        $logCallback = function (string $message) use (&$messages) {
            $messages[] = $message;
        };

        $registry = new ResolverRegistry([
            'Query' => [
                'hello' => new HelloResolver($logCallback),
            ],
        ], [
            new LogInputMiddleware($logCallback),
            new LogResultMiddleware($logCallback)
        ]);

        $result = $registry->getFieldResolver('Query', 'hello')(null, ['name' => 'Bob']);

        $this->assertEquals('Hello Bob!', $result);
        $this->assertEquals([
            '1. logInput {"name":"Bob"}',
            '2. logResult',
            '3. resolver: hello',
            '4. logResult',
            '5. logInput',
        ], $messages);
    }

    public function testResolverWithSpecificMiddleware()
    {
        $messages = [];

        $logCallback = function (string $message) use (&$messages) {
            $messages[] = $message;
        };

        $registry = new ResolverRegistry([
            'Query' => [
                'hello' => new HelloResolverWithSpecificMiddleware($logCallback),
            ],
        ], [
            new LogInputMiddleware($logCallback),
            new LogResultMiddleware($logCallback)
        ]);

        $registry->getFieldResolver('Query', 'hello')(null, ['name' => 'Bob']);

        $this->assertEquals([
            '1. logInput {"name":"Bob"}',
            '3. resolver: hello',
            '5. logInput',
        ], $messages);
    }
}

abstract class LogMiddleware implements ResolverMiddlewareInterface
{
    protected $logCallback;

    public function __construct(callable $logCallback)
    {
        $this->logCallback = $logCallback;
    }
}

class LogInputMiddleware extends LogMiddleware
{
    /**
     * @inheritdoc
     */
    public function resolve(
        callable $resolveCallback,
        $rootValue,
        array $arguments,
        $context = null,
        ?ResolveInfo $info = null
    ) {
        \call_user_func($this->logCallback, \sprintf('1. logInput %s', \json_encode($arguments)));
        $result = $resolveCallback($rootValue, $arguments, $context, $info);
        \call_user_func($this->logCallback, '5. logInput');
        return $result;
    }
}

class LogResultMiddleware extends LogMiddleware
{
    /**
     * @inheritdoc
     */
    public function resolve(
        callable $resolveCallback,
        $rootValue,
        array $arguments,
        $context = null,
        ?ResolveInfo $info = null
    ) {
        \call_user_func($this->logCallback, '2. logResult');
        $result = $resolveCallback($rootValue, $arguments, $context, $info);
        \call_user_func($this->logCallback, '4. logResult');
        return $result;
    }
}

class QueryResolver extends AbstractTypeResolver
{
    public function resolveHuman($rootValue, array $arguments, $context = null, ?ResolveInfo $info = null): array
    {
        return getHuman($arguments['id']);
    }
}

class HumanResolver extends AbstractFieldResolver
{
    public function resolve($rootValue, array $arguments, $context = null, ?ResolveInfo $info = null): array
    {
        return getHuman($arguments['id']);
    }
}

class HelloResolver extends AbstractFieldResolver
{
    protected $logCallback;

    public function __construct(callable $logCallback)
    {
        $this->logCallback = $logCallback;
    }

    public function resolve($rootValue, array $arguments, $context = null, ?ResolveInfo $info = null): string
    {
        \call_user_func($this->logCallback, '3. resolver: hello');
        return \sprintf('Hello %s!', $arguments['name'] ?? 'world');
    }
}

class HelloResolverWithSpecificMiddleware extends HelloResolver
{
    public function getMiddleware(): ?array
    {
        return [
            LogInputMiddleware::class,
        ];
    }
}
