<?php

namespace codesaur\Container\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Container\Container;
use codesaur\Container\NotFoundException;

/**
 * Integration tests for Container class
 * 
 * These tests verify the container works correctly in realistic application scenarios,
 * combining multiple features and testing end-to-end workflows.
 */
class IntegrationTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    /**
     * Integration test: Complete application setup scenario
     * 
     * Tests a realistic scenario where multiple services are registered
     * and used together, simulating a real application bootstrap.
     */
    public function testCompleteApplicationSetup(): void
    {
        // Register configuration service
        $this->container->set('config', function () {
            return [
                'app_name' => 'Test App',
                'debug' => true,
                'database' => [
                    'host' => 'localhost',
                    'port' => 3306,
                    'name' => 'testdb',
                ],
            ];
        });

        // Register database connection service
        $this->container->set('database', function (Container $c) {
            $config = $c->get('config');
            return new DatabaseConnection(
                $config['database']['host'],
                $config['database']['port'],
                $config['database']['name']
            );
        });

        // Register logger service
        $this->container->set('logger', function (Container $c) {
            $config = $c->get('config');
            return new Logger($config['app_name'], $config['debug']);
        });

        // Register user service that depends on database and logger
        $this->container->set('user_service', function (Container $c) {
            $db = $c->get('database');
            $logger = $c->get('logger');
            return new UserService($db, $logger);
        });

        // Verify all services can be retrieved
        $config = $this->container->get('config');
        $this->assertIsArray($config);
        $this->assertEquals('Test App', $config['app_name']);

        $database = $this->container->get('database');
        $this->assertInstanceOf(DatabaseConnection::class, $database);
        $this->assertEquals('localhost', $database->getHost());

        $logger = $this->container->get('logger');
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('Test App', $logger->getAppName());

        $userService = $this->container->get('user_service');
        $this->assertInstanceOf(UserService::class, $userService);
        $this->assertInstanceOf(DatabaseConnection::class, $userService->getDatabase());
        $this->assertInstanceOf(Logger::class, $userService->getLogger());
    }

    /**
     * Integration test: Service replacement scenario
     * 
     * Tests removing and re-registering services, which might happen
     * during testing or configuration changes.
     */
    public function testServiceReplacement(): void
    {
        // Register initial service
        $this->container->set('service', function () {
            return new TestService('initial');
        });

        $service1 = $this->container->get('service');
        $this->assertEquals('initial', $service1->getValue());

        // Remove and replace with new implementation
        $this->container->remove('service');
        $this->container->set('service', function () {
            return new TestService('replaced');
        });

        $service2 = $this->container->get('service');
        $this->assertEquals('replaced', $service2->getValue());
        $this->assertNotSame($service1, $service2);
    }

    /**
     * Integration test: Singleton behavior across multiple services
     * 
     * Verifies that the same instance is returned when accessing
     * a service multiple times, even when used by different services.
     */
    public function testSingletonBehaviorAcrossServices(): void
    {
        // Register shared service
        $this->container->set('shared', function () {
            return new SharedService();
        });

        // Register service A that uses shared
        $this->container->set('service_a', function (Container $c) {
            return new ServiceA($c->get('shared'));
        });

        // Register service B that also uses shared
        $this->container->set('service_b', function (Container $c) {
            return new ServiceB($c->get('shared'));
        });

        $serviceA = $this->container->get('service_a');
        $serviceB = $this->container->get('service_b');
        $shared1 = $this->container->get('shared');
        $shared2 = $this->container->get('shared');

        // Verify same instance is returned
        $this->assertSame($shared1, $shared2);
        $this->assertSame($shared1, $serviceA->getShared());
        $this->assertSame($shared1, $serviceB->getShared());
    }

    /**
     * Integration test: Complex dependency chain
     * 
     * Tests a scenario with multiple levels of dependencies.
     */
    public function testComplexDependencyChain(): void
    {
        // Level 1: Base service
        $this->container->set('base', function () {
            return new BaseService();
        });

        // Level 2: Depends on base
        $this->container->set('middle', function (Container $c) {
            return new MiddleService($c->get('base'));
        });

        // Level 3: Depends on middle and base
        $this->container->set('top', function (Container $c) {
            return new TopService($c->get('middle'), $c->get('base'));
        });

        $top = $this->container->get('top');
        $this->assertInstanceOf(TopService::class, $top);
        $this->assertInstanceOf(MiddleService::class, $top->getMiddle());
        $this->assertInstanceOf(BaseService::class, $top->getBase());
        $this->assertInstanceOf(BaseService::class, $top->getMiddle()->getBase());
    }

    /**
     * Integration test: Mixed registration types
     * 
     * Tests using both class-based and callable-based registrations
     * in the same container setup.
     */
    public function testMixedRegistrationTypes(): void
    {
        // Register class directly
        $this->container->set(SimpleService::class, ['test-value']);

        // Register callable
        $this->container->set('callable_service', function (Container $c) {
            $simple = $c->get(SimpleService::class);
            return new WrapperService($simple);
        });

        $simple = $this->container->get(SimpleService::class);
        $this->assertInstanceOf(SimpleService::class, $simple);
        $this->assertEquals('test-value', $simple->getValue());

        $wrapper = $this->container->get('callable_service');
        $this->assertInstanceOf(WrapperService::class, $wrapper);
        $this->assertSame($simple, $wrapper->getWrapped());
    }

    /**
     * Integration test: Error handling in dependency chain
     * 
     * Tests that errors are properly propagated when a dependency
     * is missing in a callable factory.
     */
    public function testErrorHandlingInDependencyChain(): void
    {
        // Register service that depends on non-existent service
        $this->container->set('broken', function (Container $c) {
            return new BrokenService($c->get('non_existent'));
        });

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Entry not found: non_existent');

        $this->container->get('broken');
    }

    /**
     * Integration test: Lazy loading in complex scenario
     * 
     * Verifies that lazy loading works correctly even when services
     * have complex dependency relationships.
     */
    public function testLazyLoadingInComplexScenario(): void
    {
        // Reset counters
        LazyService::$instantiationCount = 0;
        LazyDependency::$instantiationCount = 0;

        // Register services
        $this->container->set('lazy_dep', function () {
            return new LazyDependency();
        });

        $this->container->set('lazy_service', function (Container $c) {
            return new LazyService($c->get('lazy_dep'));
        });

        // Verify nothing is instantiated yet
        $this->assertEquals(0, LazyService::$instantiationCount);
        $this->assertEquals(0, LazyDependency::$instantiationCount);

        // Get service - should instantiate both
        $service = $this->container->get('lazy_service');
        $this->assertEquals(1, LazyService::$instantiationCount);
        $this->assertEquals(1, LazyDependency::$instantiationCount);

        // Get again - should use cached instances
        $service2 = $this->container->get('lazy_service');
        $this->assertEquals(1, LazyService::$instantiationCount);
        $this->assertEquals(1, LazyDependency::$instantiationCount);
        $this->assertSame($service, $service2);
    }
}

/**
 * Test helper classes for integration tests
 */
class DatabaseConnection
{
    private string $host;
    private int $port;
    private string $name;

    public function __construct(string $host, int $port, string $name)
    {
        $this->host = $host;
        $this->port = $port;
        $this->name = $name;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

class Logger
{
    private string $appName;
    private bool $debug;

    public function __construct(string $appName, bool $debug)
    {
        $this->appName = $appName;
        $this->debug = $debug;
    }

    public function getAppName(): string
    {
        return $this->appName;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }
}

class UserService
{
    private DatabaseConnection $database;
    private Logger $logger;

    public function __construct(DatabaseConnection $database, Logger $logger)
    {
        $this->database = $database;
        $this->logger = $logger;
    }

    public function getDatabase(): DatabaseConnection
    {
        return $this->database;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }
}

class TestService
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

class SharedService
{
}

class ServiceA
{
    private SharedService $shared;

    public function __construct(SharedService $shared)
    {
        $this->shared = $shared;
    }

    public function getShared(): SharedService
    {
        return $this->shared;
    }
}

class ServiceB
{
    private SharedService $shared;

    public function __construct(SharedService $shared)
    {
        $this->shared = $shared;
    }

    public function getShared(): SharedService
    {
        return $this->shared;
    }
}

class BaseService
{
}

class MiddleService
{
    private BaseService $base;

    public function __construct(BaseService $base)
    {
        $this->base = $base;
    }

    public function getBase(): BaseService
    {
        return $this->base;
    }
}

class TopService
{
    private MiddleService $middle;
    private BaseService $base;

    public function __construct(MiddleService $middle, BaseService $base)
    {
        $this->middle = $middle;
        $this->base = $base;
    }

    public function getMiddle(): MiddleService
    {
        return $this->middle;
    }

    public function getBase(): BaseService
    {
        return $this->base;
    }
}

class SimpleService
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

class WrapperService
{
    private SimpleService $wrapped;

    public function __construct(SimpleService $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function getWrapped(): SimpleService
    {
        return $this->wrapped;
    }
}

class BrokenService
{
    public function __construct($dependency)
    {
        // This will never be reached if dependency doesn't exist
    }
}

class LazyDependency
{
    public static int $instantiationCount = 0;

    public function __construct()
    {
        self::$instantiationCount++;
    }
}

class LazyService
{
    public static int $instantiationCount = 0;
    private LazyDependency $dependency;

    public function __construct(LazyDependency $dependency)
    {
        self::$instantiationCount++;
        $this->dependency = $dependency;
    }

    public function getDependency(): LazyDependency
    {
        return $this->dependency;
    }
}
