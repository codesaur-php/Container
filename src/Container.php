<?php

namespace codesaur\Container;

use ReflectionClass;
use Psr\Container\ContainerInterface;

/**
 * Class Container
 *
 * Хөнгөн жинтэй dependency injection контейнер.
 * PSR-11 ContainerInterface стандартыг хэрэгжүүлдэг.
 *
 * Энэ контейнер нь зөвхөн **класс нэрээр** service бүртгэж,
 * Reflection ашиглаж constructor-ын аргументаар instance үүсгэдэг.
 *
 * @package codesaur\Container
 */
class Container implements ContainerInterface
{
    /**
     * Контейнерт хадгалагдаж буй бүх сервисүүд.
     *
     * @var array<string, mixed>
     */
    protected $entries = [];

    /**
     * ID нэрээр сервис авах.
     *
     * @param string $name Класс эсвэл сервисийн ID
     * @return mixed Бүртгэлтэй сервисийн instance
     *
     * @throws NotFoundException   Сервис олдохгүй бол
     */
    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new NotFoundException('Entry not found: ' . $name);
        }
        
        return $this->entries[$name];
    }
    
    /**
     * Тухайн нэртэй сервис бүртгэлтэй эсэхийг шалгах.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->entries[$name]);
    }
    
    /**
     * Контейнерт шинэ сервис бүртгэх.
     *
     * Анхаарах зүйлс:
     * - $name параметр нь заавал **класс нэр** байх ёстой.
     * - Класс байхгүй бол NotFoundException шиднэ.
     * - Давхар бүртгэхийг хориглоно.
     * - ReflectionClass ашиглаж constructor-ын аргументуудаар instance үүсгэнэ.
     *
     * @param string $name  Бүртгэх класс нэр
     * @param array  $args  Класс үүсгэх constructor аргументууд
     *
     * @throws NotFoundException     Класс байхгүй бол
     * @throws ContainerException    Давхар бүртгэх үед
     *
     * @return void
     */
    public function set(string $name, array $args = [])
    {
        if (!\class_exists($name)) {
            throw new NotFoundException($name . ' class does not exist');
        } elseif ($this->has($name)) {
            throw new ContainerException(__CLASS__ . ' already contains entry named [' . $name . ']');
        }
        
        $reflector = new ReflectionClass($name);
        $this->entries[$name] = $reflector->newInstanceArgs($args);
    }

    /**
     * Контейнерээс сервис устгах
     *
     * @param string $name
     * @return void
     */
    public function remove(string $name): void
    {
        if ($this->has($name)) {
            unset($this->entries[$name]);
        }
    }
}
