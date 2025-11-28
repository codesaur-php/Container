<?php

namespace codesaur\Container;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 *
 * Контейнер дотор шаардсан service эсвэл entry олдохгүй үед шидэгддэг exception.
 *
 * Энэ нь PSR-11 стандартын `NotFoundExceptionInterface`-ийг
 * хэрэгжүүлдэг бөгөөд контейнер доторх "байхгүй service"-ийг илэрхийлэх
 * албан ёсны алдааны төрөл юм.
 *
 * Ашиглагдах тохиолдлууд:
 * - `get($name)` дуудах үед тухайн нэртэй service бүртгэгдээгүй бол
 * - `set()` хийх үед класс олдохгүй бол
 * - Контейнерээс авах гэж буй ID буруу эсвэл оршин байхгүй бол
 *
 * @package codesaur\Container
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
