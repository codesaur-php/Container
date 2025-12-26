<?php

namespace codesaur\Container\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Container\Container;
use codesaur\Container\ContainerException;
use codesaur\Container\NotFoundException;

/**
 * Unit tests for Container class
 */
class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    /**
     * Test basic service registration and retrieval
     */
    public function testSetAndGet(): void
    {
        $this->container->set(SimpleClass::class);
        $instance = $this->container->get(SimpleClass::class);

        $this->assertInstanceOf(SimpleClass::class, $instance);
    }

    /**
     * Test that get() returns the same instance (singleton behavior)
     */
    public function testGetReturnsSameInstance(): void
    {
        $this->container->set(SimpleClass::class);
        $instance1 = $this->container->get(SimpleClass::class);
        $instance2 = $this->container->get(SimpleClass::class);

        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test has() method returns true for registered services
     */
    public function testHasReturnsTrueForRegisteredService(): void
    {
        $this->container->set(SimpleClass::class);
        $this->assertTrue($this->container->has(SimpleClass::class));
    }

    /**
     * Test has() method returns false for unregistered services
     */
    public function testHasReturnsFalseForUnregisteredService(): void
    {
        $this->assertFalse($this->container->has(SimpleClass::class));
    }

    /**
     * Test get() throws NotFoundException for unregistered service
     */
    public function testGetThrowsNotFoundExceptionForUnregisteredService(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Entry not found: ' . SimpleClass::class);

        $this->container->get(SimpleClass::class);
    }

    /**
     * Test set() with constructor arguments
     */
    public function testSetWithConstructorArguments(): void
    {
        $text = 'Hello, World!';
        $this->container->set(ClassWithConstructor::class, [$text]);
        $instance = $this->container->get(ClassWithConstructor::class);

        $this->assertInstanceOf(ClassWithConstructor::class, $instance);
        $this->assertEquals($text, $instance->getValue());
    }

    /**
     * Test set() with multiple constructor arguments
     */
    public function testSetWithMultipleConstructorArguments(): void
    {
        $arg1 = 'test';
        $arg2 = 123;
        $arg3 = true;

        $this->container->set(ClassWithMultipleArgs::class, [$arg1, $arg2, $arg3]);
        $instance = $this->container->get(ClassWithMultipleArgs::class);

        $this->assertEquals($arg1, $instance->getArg1());
        $this->assertEquals($arg2, $instance->getArg2());
        $this->assertEquals($arg3, $instance->getArg3());
    }

    /**
     * Test set() throws NotFoundException for non-existent class
     */
    public function testSetThrowsNotFoundExceptionForNonExistentClass(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('NonExistentClass class does not exist');

        $this->container->set('NonExistentClass');
    }

    /**
     * Test set() throws ContainerException for duplicate registration
     */
    public function testSetThrowsContainerExceptionForDuplicateRegistration(): void
    {
        $this->container->set(SimpleClass::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('already contains entry named');

        $this->container->set(SimpleClass::class);
    }

    /**
     * Test remove() removes a registered service
     */
    public function testRemoveRemovesService(): void
    {
        $this->container->set(SimpleClass::class);
        $this->assertTrue($this->container->has(SimpleClass::class));

        $this->container->remove(SimpleClass::class);
        $this->assertFalse($this->container->has(SimpleClass::class));
    }

    /**
     * Test remove() on non-existent service does not throw
     */
    public function testRemoveOnNonExistentServiceDoesNotThrow(): void
    {
        $this->container->remove(SimpleClass::class);
        $this->assertFalse($this->container->has(SimpleClass::class));
    }

    /**
     * Test set() with callable/closure
     */
    public function testSetWithCallable(): void
    {
        $value = 'test value';
        $this->container->set('test_key', function () use ($value) {
            return $value;
        });

        $result = $this->container->get('test_key');
        $this->assertEquals($value, $result);
    }

    /**
     * Test set() with callable that receives container
     */
    public function testSetWithCallableReceivingContainer(): void
    {
        $this->container->set('dependency', function () {
            return new SimpleClass();
        });

        $this->container->set('service', function (Container $container) {
            $dep = $container->get('dependency');
            return new ClassWithDependency($dep);
        });

        $service = $this->container->get('service');
        $this->assertInstanceOf(ClassWithDependency::class, $service);
        $this->assertInstanceOf(SimpleClass::class, $service->getDependency());
    }

    /**
     * Test set() with empty array as default
     */
    public function testSetWithEmptyArrayDefault(): void
    {
        $this->container->set(SimpleClass::class, []);
        $instance = $this->container->get(SimpleClass::class);

        $this->assertInstanceOf(SimpleClass::class, $instance);
    }

    /**
     * Test PSR-11 ContainerInterface compliance - has() method
     */
    public function testPsr11HasMethod(): void
    {
        $this->assertFalse($this->container->has('NonExistent'));

        $this->container->set(SimpleClass::class);
        $this->assertTrue($this->container->has(SimpleClass::class));
    }

    /**
     * Test PSR-11 ContainerInterface compliance - get() method
     */
    public function testPsr11GetMethod(): void
    {
        $this->container->set(SimpleClass::class);
        $instance = $this->container->get(SimpleClass::class);

        $this->assertInstanceOf(SimpleClass::class, $instance);
    }

    /**
     * Test that exceptions implement PSR-11 interfaces
     */
    public function testExceptionsImplementPsr11Interfaces(): void
    {
        $notFoundException = new NotFoundException();
        $this->assertInstanceOf(\Psr\Container\NotFoundExceptionInterface::class, $notFoundException);

        $containerException = new ContainerException();
        $this->assertInstanceOf(\Psr\Container\ContainerExceptionInterface::class, $containerException);
    }

    /**
     * Test class with no constructor
     */
    public function testClassWithNoConstructor(): void
    {
        $this->container->set(ClassWithNoConstructor::class);
        $instance = $this->container->get(ClassWithNoConstructor::class);

        $this->assertInstanceOf(ClassWithNoConstructor::class, $instance);
    }

    /**
     * Test class with optional constructor parameters
     */
    public function testClassWithOptionalConstructorParameters(): void
    {
        $this->container->set(ClassWithOptionalArgs::class);
        $instance = $this->container->get(ClassWithOptionalArgs::class);

        $this->assertInstanceOf(ClassWithOptionalArgs::class, $instance);
        $this->assertEquals('default', $instance->getValue());
    }

    /**
     * Test class with optional constructor parameters can be overridden
     */
    public function testClassWithOptionalConstructorParametersCanBeOverridden(): void
    {
        $customValue = 'custom';
        $this->container->set(ClassWithOptionalArgs::class, [$customValue]);
        $instance = $this->container->get(ClassWithOptionalArgs::class);

        $this->assertEquals($customValue, $instance->getValue());
    }

    /**
     * Test remove and re-register service
     */
    public function testRemoveAndReregisterService(): void
    {
        $this->container->set(SimpleClass::class);
        $instance1 = $this->container->get(SimpleClass::class);

        $this->container->remove(SimpleClass::class);
        $this->container->set(SimpleClass::class);
        $instance2 = $this->container->get(SimpleClass::class);

        $this->assertNotSame($instance1, $instance2);
    }

    /**
     * Test lazy loading - service is not instantiated until get() is called
     */
    public function testLazyLoadingServiceNotInstantiatedOnSet(): void
    {
        // Reset the static counter
        LazyLoadTestClass::$instantiationCount = 0;

        // set() should not instantiate the class
        $this->container->set(LazyLoadTestClass::class);
        $this->assertEquals(0, LazyLoadTestClass::$instantiationCount);

        // get() should instantiate the class
        $instance = $this->container->get(LazyLoadTestClass::class);
        $this->assertEquals(1, LazyLoadTestClass::$instantiationCount);
        $this->assertInstanceOf(LazyLoadTestClass::class, $instance);
    }

    /**
     * Test lazy loading - service is cached after first get()
     */
    public function testLazyLoadingServiceCachedAfterFirstGet(): void
    {
        // Reset the static counter
        LazyLoadTestClass::$instantiationCount = 0;

        $this->container->set(LazyLoadTestClass::class);

        // First get() should instantiate
        $instance1 = $this->container->get(LazyLoadTestClass::class);
        $this->assertEquals(1, LazyLoadTestClass::$instantiationCount);

        // Second get() should return cached instance (no new instantiation)
        $instance2 = $this->container->get(LazyLoadTestClass::class);
        $this->assertEquals(1, LazyLoadTestClass::$instantiationCount);
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test lazy loading with callable
     */
    public function testLazyLoadingWithCallable(): void
    {
        $callableCalled = false;

        $this->container->set('lazy_service', function () use (&$callableCalled) {
            $callableCalled = true;
            return new SimpleClass();
        });

        // Callable should not be called on set()
        $this->assertFalse($callableCalled);

        // Callable should be called on get()
        $instance = $this->container->get('lazy_service');
        $this->assertTrue($callableCalled);
        $this->assertInstanceOf(SimpleClass::class, $instance);
    }

    /**
     * Test auto-wiring - automatic dependency resolution
     */
    public function testAutoWiringResolvesDependencies(): void
    {
        // Register dependency
        $this->container->set(SimpleClass::class);

        // Register class that depends on SimpleClass
        $this->container->set(ClassWithDependency::class);

        // Auto-wiring should automatically inject SimpleClass
        $service = $this->container->get(ClassWithDependency::class);
        $this->assertInstanceOf(ClassWithDependency::class, $service);
        $this->assertInstanceOf(SimpleClass::class, $service->getDependency());
    }

    /**
     * Test auto-wiring with multiple dependencies
     */
    public function testAutoWiringWithMultipleDependencies(): void
    {
        $this->container->set(SimpleClass::class);
        $this->container->set(ClassWithDependency::class);

        // Class with multiple dependencies
        $this->container->set(ClassWithMultipleDependencies::class);

        $service = $this->container->get(ClassWithMultipleDependencies::class);
        $this->assertInstanceOf(ClassWithMultipleDependencies::class, $service);
        $this->assertInstanceOf(SimpleClass::class, $service->getDep1());
        $this->assertInstanceOf(ClassWithDependency::class, $service->getDep2());
    }

    /**
     * Test auto-wiring with user-provided arguments (user args take priority)
     */
    public function testAutoWiringWithUserProvidedArguments(): void
    {
        $this->container->set(SimpleClass::class);
        
        // User provides argument, auto-wiring should not override it
        $customDep = new SimpleClass();
        $this->container->set(ClassWithDependency::class, [$customDep]);

        $service = $this->container->get(ClassWithDependency::class);
        $this->assertInstanceOf(ClassWithDependency::class, $service);
        $this->assertSame($customDep, $service->getDependency());
    }

    /**
     * Test auto-wiring with optional parameters (auto-wired when available)
     */
    public function testAutoWiringWithOptionalParameters(): void
    {
        $this->container->set(SimpleClass::class);
        $this->container->set(ClassWithOptionalDependency::class);

        $service = $this->container->get(ClassWithOptionalDependency::class);
        $this->assertInstanceOf(ClassWithOptionalDependency::class, $service);
        // Auto-wiring should inject SimpleClass even though parameter is optional
        $this->assertInstanceOf(SimpleClass::class, $service->getDependency());
    }

    /**
     * Test auto-wiring with optional parameters (uses default when dependency not registered)
     */
    public function testAutoWiringWithOptionalParametersUsesDefault(): void
    {
        // Don't register SimpleClass
        $this->container->set(ClassWithOptionalDependency::class);

        $service = $this->container->get(ClassWithOptionalDependency::class);
        $this->assertInstanceOf(ClassWithOptionalDependency::class, $service);
        // Should use default value (null) when dependency not registered
        $this->assertNull($service->getDependency());
    }

    /**
     * Test auto-wiring throws exception when dependency not found
     */
    public function testAutoWiringThrowsExceptionWhenDependencyNotFound(): void
    {
        // Register class with dependency, but don't register the dependency
        $this->container->set(ClassWithDependency::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Cannot resolve parameter');

        $this->container->get(ClassWithDependency::class);
    }

    /**
     * Test auto-wiring with mixed dependencies (some auto-wired, some provided)
     */
    public function testAutoWiringWithMixedDependencies(): void
    {
        $this->container->set(SimpleClass::class);
        
        // First arg provided, second should be auto-wired
        $this->container->set(ClassWithMixedDependencies::class, ['custom-value']);

        $service = $this->container->get(ClassWithMixedDependencies::class);
        $this->assertInstanceOf(ClassWithMixedDependencies::class, $service);
        $this->assertEquals('custom-value', $service->getValue());
        $this->assertInstanceOf(SimpleClass::class, $service->getDependency());
    }

    /**
     * Test interface binding - bind interface to implementation
     */
    public function testInterfaceBinding(): void
    {
        $this->container->bind(LoggerInterface::class, FileLogger::class);
        $this->container->set(FileLogger::class, ['/var/log/app.log']);

        // Interface-ээр авахад implementation instance буцаана
        $logger = $this->container->get(LoggerInterface::class);
        $this->assertInstanceOf(FileLogger::class, $logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    /**
     * Test interface binding with auto-wiring
     */
    public function testInterfaceBindingWithAutoWiring(): void
    {
        $this->container->bind(LoggerInterface::class, FileLogger::class);
        $this->container->set(FileLogger::class, ['/var/log/app.log']);

        // Service that depends on LoggerInterface
        $this->container->set(ServiceWithLoggerInterface::class);

        $service = $this->container->get(ServiceWithLoggerInterface::class);
        $this->assertInstanceOf(ServiceWithLoggerInterface::class, $service);
        $this->assertInstanceOf(FileLogger::class, $service->getLogger());
        $this->assertInstanceOf(LoggerInterface::class, $service->getLogger());
    }

    /**
     * Test interface binding throws exception when interface does not exist
     */
    public function testInterfaceBindingThrowsExceptionWhenInterfaceNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('NonExistentInterface interface does not exist');

        $this->container->bind('NonExistentInterface', SimpleClass::class);
    }

    /**
     * Test interface binding throws exception when implementation does not exist
     */
    public function testInterfaceBindingThrowsExceptionWhenImplementationNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('NonExistentClass class does not exist');

        $this->container->bind(LoggerInterface::class, 'NonExistentClass');
    }

    /**
     * Test interface binding throws exception when class does not implement interface
     */
    public function testInterfaceBindingThrowsExceptionWhenClassDoesNotImplementInterface(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('does not implement interface');

        $this->container->bind(LoggerInterface::class, SimpleClass::class);
    }

    /**
     * Test interface binding throws exception for duplicate binding
     */
    public function testInterfaceBindingThrowsExceptionForDuplicateBinding(): void
    {
        $this->container->bind(LoggerInterface::class, FileLogger::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('already contains binding for interface');

        $this->container->bind(LoggerInterface::class, FileLogger::class);
    }

    /**
     * Test has() method with interface binding
     */
    public function testHasWithInterfaceBinding(): void
    {
        $this->container->bind(LoggerInterface::class, FileLogger::class);
        $this->container->set(FileLogger::class);

        $this->assertTrue($this->container->has(LoggerInterface::class));
        $this->assertTrue($this->container->has(FileLogger::class));
    }

    /**
     * Test interface binding with multiple implementations
     */
    public function testInterfaceBindingWithMultipleImplementations(): void
    {
        $this->container->bind(LoggerInterface::class, FileLogger::class);
        $this->container->set(FileLogger::class, ['/var/log/app.log']);

        // Change binding to different implementation
        $this->container->remove(LoggerInterface::class);
        $this->container->bind(LoggerInterface::class, DatabaseLogger::class);
        $this->container->set(DatabaseLogger::class, ['localhost', 'logs']);

        $logger = $this->container->get(LoggerInterface::class);
        $this->assertInstanceOf(DatabaseLogger::class, $logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    /**
     * Test service alias - create alias for service
     */
    public function testServiceAlias(): void
    {
        $this->container->set(SimpleClass::class);
        $this->container->alias('simple', SimpleClass::class);

        // Alias-ээр авахад ижил instance буцаана
        $service1 = $this->container->get(SimpleClass::class);
        $service2 = $this->container->get('simple');

        $this->assertSame($service1, $service2);
        $this->assertInstanceOf(SimpleClass::class, $service2);
    }

    /**
     * Test service alias with has() method
     */
    public function testServiceAliasWithHas(): void
    {
        $this->container->set(SimpleClass::class);
        $this->container->alias('simple', SimpleClass::class);

        $this->assertTrue($this->container->has('simple'));
        $this->assertTrue($this->container->has(SimpleClass::class));
    }

    /**
     * Test service alias throws exception when service not found
     */
    public function testServiceAliasThrowsExceptionWhenServiceNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Cannot create alias: service');

        $this->container->alias('simple', 'NonExistentService');
    }

    /**
     * Test service alias throws exception for duplicate alias
     */
    public function testServiceAliasThrowsExceptionForDuplicateAlias(): void
    {
        $this->container->set(SimpleClass::class);
        $this->container->alias('simple', SimpleClass::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('already contains alias named');

        $this->container->alias('simple', SimpleClass::class);
    }

    /**
     * Test service alias throws exception when alias name equals service name
     */
    public function testServiceAliasThrowsExceptionWhenAliasEqualsServiceName(): void
    {
        $this->container->set(SimpleClass::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('cannot be the same as service name');

        $this->container->alias(SimpleClass::class, SimpleClass::class);
    }

    /**
     * Test service alias with multiple aliases
     */
    public function testServiceAliasWithMultipleAliases(): void
    {
        $this->container->set(SimpleClass::class);
        $this->container->alias('simple', SimpleClass::class);
        $this->container->alias('simple_service', SimpleClass::class);
        $this->container->alias('app.simple', SimpleClass::class);

        $service1 = $this->container->get('simple');
        $service2 = $this->container->get('simple_service');
        $service3 = $this->container->get('app.simple');
        $service4 = $this->container->get(SimpleClass::class);

        // Бүх alias-үүд ижил instance буцаана
        $this->assertSame($service1, $service2);
        $this->assertSame($service2, $service3);
        $this->assertSame($service3, $service4);
    }

    /**
     * Test service alias with interface binding
     */
    public function testServiceAliasWithInterfaceBinding(): void
    {
        $this->container->bind(LoggerInterface::class, FileLogger::class);
        $this->container->set(FileLogger::class, ['/var/log/app.log']);
        $this->container->alias('logger', LoggerInterface::class);

        $logger1 = $this->container->get(LoggerInterface::class);
        $logger2 = $this->container->get('logger');

        $this->assertSame($logger1, $logger2);
        $this->assertInstanceOf(FileLogger::class, $logger2);
    }

    /**
     * Test service alias with auto-wiring
     */
    public function testServiceAliasWithAutoWiring(): void
    {
        $this->container->set(SimpleClass::class);
        $this->container->alias('dep', SimpleClass::class);

        // Service that depends on SimpleClass
        $this->container->set(ClassWithDependency::class);

        $service = $this->container->get(ClassWithDependency::class);
        $this->assertInstanceOf(ClassWithDependency::class, $service);
        // Auto-wiring should use SimpleClass, not alias
        $this->assertInstanceOf(SimpleClass::class, $service->getDependency());
    }

    /**
     * Test service alias removal
     */
    public function testServiceAliasRemoval(): void
    {
        $this->container->set(SimpleClass::class);
        $this->container->alias('simple', SimpleClass::class);

        $this->assertTrue($this->container->has('simple'));

        // Alias устгах
        $this->container->remove('simple');

        // Alias устгагдсан
        $this->assertFalse($this->container->has('simple'));
        // Бодит сервис хэвээр байна
        $this->assertTrue($this->container->has(SimpleClass::class));
    }
}

/**
 * Test helper classes
 */
class SimpleClass
{
}

class ClassWithConstructor
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

class ClassWithMultipleArgs
{
    private string $arg1;
    private int $arg2;
    private bool $arg3;

    public function __construct(string $arg1, int $arg2, bool $arg3)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
        $this->arg3 = $arg3;
    }

    public function getArg1(): string
    {
        return $this->arg1;
    }

    public function getArg2(): int
    {
        return $this->arg2;
    }

    public function getArg3(): bool
    {
        return $this->arg3;
    }
}

class ClassWithDependency
{
    private SimpleClass $dependency;

    public function __construct(SimpleClass $dependency)
    {
        $this->dependency = $dependency;
    }

    public function getDependency(): SimpleClass
    {
        return $this->dependency;
    }
}

class ClassWithNoConstructor
{
}

class ClassWithOptionalArgs
{
    private string $value;

    public function __construct(string $value = 'default')
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

class LazyLoadTestClass
{
    public static int $instantiationCount = 0;

    public function __construct()
    {
        self::$instantiationCount++;
    }
}

class ClassWithMultipleDependencies
{
    private SimpleClass $dep1;
    private ClassWithDependency $dep2;

    public function __construct(SimpleClass $dep1, ClassWithDependency $dep2)
    {
        $this->dep1 = $dep1;
        $this->dep2 = $dep2;
    }

    public function getDep1(): SimpleClass
    {
        return $this->dep1;
    }

    public function getDep2(): ClassWithDependency
    {
        return $this->dep2;
    }
}

class ClassWithOptionalDependency
{
    private ?SimpleClass $dependency;

    public function __construct(SimpleClass $dependency = null)
    {
        $this->dependency = $dependency;
    }

    public function getDependency(): ?SimpleClass
    {
        return $this->dependency;
    }
}

class ClassWithMixedDependencies
{
    private string $value;
    private SimpleClass $dependency;

    public function __construct(string $value, SimpleClass $dependency)
    {
        $this->value = $value;
        $this->dependency = $dependency;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDependency(): SimpleClass
    {
        return $this->dependency;
    }
}

interface LoggerInterface
{
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function log(string $message): void
    {
        // Log implementation
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}

class DatabaseLogger implements LoggerInterface
{
    private string $host;
    private string $database;

    public function __construct(string $host, string $database)
    {
        $this->host = $host;
        $this->database = $database;
    }

    public function log(string $message): void
    {
        // Log implementation
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }
}

class ServiceWithLoggerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
