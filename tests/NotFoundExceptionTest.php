<?php

namespace codesaur\Container\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Container\NotFoundException;

/**
 * Unit tests for NotFoundException class
 */
class NotFoundExceptionTest extends TestCase
{
    /**
     * Test that NotFoundException extends Exception
     */
    public function testExtendsException(): void
    {
        $exception = new NotFoundException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * Test that NotFoundException implements NotFoundExceptionInterface
     */
    public function testImplementsNotFoundExceptionInterface(): void
    {
        $exception = new NotFoundException();
        $this->assertInstanceOf(\Psr\Container\NotFoundExceptionInterface::class, $exception);
    }

    /**
     * Test exception with message
     */
    public function testExceptionWithMessage(): void
    {
        $message = 'Service not found';
        $exception = new NotFoundException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * Test exception with message and code
     */
    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'Service not found';
        $code = 404;
        $exception = new NotFoundException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * Test exception with previous exception
     */
    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new NotFoundException('Service not found', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
