<?php

namespace codesaur\Container\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Container\ContainerException;

/**
 * Unit tests for ContainerException class
 */
class ContainerExceptionTest extends TestCase
{
    /**
     * Test that ContainerException extends Exception
     */
    public function testExtendsException(): void
    {
        $exception = new ContainerException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * Test that ContainerException implements ContainerExceptionInterface
     */
    public function testImplementsContainerExceptionInterface(): void
    {
        $exception = new ContainerException();
        $this->assertInstanceOf(\Psr\Container\ContainerExceptionInterface::class, $exception);
    }

    /**
     * Test exception with message
     */
    public function testExceptionWithMessage(): void
    {
        $message = 'Test error message';
        $exception = new ContainerException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * Test exception with message and code
     */
    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'Test error message';
        $code = 500;
        $exception = new ContainerException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * Test exception with previous exception
     */
    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new ContainerException('Container error', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
