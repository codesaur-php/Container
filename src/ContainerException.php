<?php

namespace codesaur\Container;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * Class ContainerException
 *
 * Контейнерийн ажлын явцад гарч болох алдааг илэрхийлэх Exception.
 *
 * Энэ exception нь PSR-11-ийн `ContainerExceptionInterface`-ийг
 * хэрэгжүүлдэг тул контейнер доторх бүх ерөнхий алдааны төлөөлөл болно.
 *
 * Ашиглагдах тохиолдлууд:
 * - Service бүртгэх үед давхардсан нэртэй байвал
 * - Reflection ашиглан объект үүсгэх явцад алдаа гарвал
 * - Container доторх аливаа дотоод логик амжилтгүй болвол
 *
 * @package codesaur\Container
 */
class ContainerException extends Exception implements ContainerExceptionInterface
{
}
