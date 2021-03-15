<?php declare(strict_types=1);

namespace codesaur\Container;

use Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
