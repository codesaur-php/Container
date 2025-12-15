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
