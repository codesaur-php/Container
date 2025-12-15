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
 * Энэ контейнер нь зөвхөн <b>класс нэрээр</b> service бүртгэж,
 * Reflection ашиглаж constructor-ын аргументаар instance үүсгэдэг.
 *
 * <b>Lazy Loading:</b> Сервисүүд зөвхөн шаардлагатай үед (get() дуудагдах үед)
 * үүсгэгдэнэ. Энэ нь хүнд сервисүүдийн хувьд гүйцэтгэлийг сайжруулна.
 *
 * @package codesaur\Container
 */
class Container implements ContainerInterface
{
    /**
     * Контейнерт хадгалагдаж буй бүх сервисүүдийн тодорхойлолтууд.
     * Lazy loading-ийн тусламжтайгаар зөвхөн шаардлагатай үед instance үүсгэнэ.
     *
     * @var array<string, mixed>
     */
    protected $definitions = [];

    /**
     * Үүсгэгдсэн instance-үүдийн кэш.
     * Нэг удаа үүсгэсэн instance-ийг дахин ашиглана (singleton pattern).
     *
     * @var array<string, mixed>
     */
    protected $instances = [];

    /**
     * ID нэрээр сервис авах.
     * 
     * Lazy loading: Сервис зөвхөн эхний удаа дуудагдах үед үүсгэгдэнэ.
     * Дараагийн дуудлагуудад кэшлэгдсэн instance буцаана.
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

        // Кэшлэгдсэн instance байвал буцаана
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Lazy loading: зөвхөн одоо instance үүсгэнэ
        $definition = $this->definitions[$name];
        
        if (\is_callable($definition)) {
            // Callable бол container-ийг дамжуулж дуудна
            $instance = $definition($this);
        } else {
            // Класс нэр бол Reflection ашиглан instance үүсгэнэ
            $reflector = new ReflectionClass($name);
            $instance = $reflector->newInstanceArgs($definition);
        }

        // Кэшлэх (singleton behavior)
        $this->instances[$name] = $instance;
        
        return $instance;
    }
    
    /**
     * Тухайн нэртэй сервис бүртгэлтэй эсэхийг шалгах.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->definitions[$name]);
    }
    
    /**
     * Контейнерт шинэ сервис бүртгэх.
     *
     * Lazy loading: Сервис одоо үүсгэгдэхгүй, зөвхөн тодорхойлолт хадгалагдана.
     * Instance нь анх удаа get() дуудагдах үед үүсгэгдэнэ.
     *
     * Анхаарах зүйлс:
     * - $name параметр нь заавал <b>класс нэр</b> байх ёстой (callable-ийн хувьд аль ч string байж болно).
     * - Класс байхгүй бол NotFoundException шиднэ.
     * - Давхар бүртгэхийг хориглоно.
     * - ReflectionClass ашиглаж constructor-ын аргументуудаар instance үүсгэнэ (get() дуудагдах үед).
     *
     * @param string $name        Бүртгэх класс нэр эсвэл сервисийн ID
     * @param mixed  $definition  Класс үүсгэх constructor аргументууд (array) эсвэл callable Closure
     *
     * @throws NotFoundException     Класс байхгүй бол
     * @throws ContainerException    Давхар бүртгэх үед
     *
     * @return void
     */
    public function set(string $name, mixed $definition = [])
    {
        // Callable бол шууд хадгална (lazy loading)
        if (\is_callable($definition)) {
            if ($this->has($name)) {
                throw new ContainerException(__CLASS__ . ' already contains entry named [' . $name . ']');
            }
            $this->definitions[$name] = $definition;
            return;
        }
    
        // Класс байхгүй бол алдаа
        if (!\class_exists($name)) {
            throw new NotFoundException($name . ' class does not exist');
        }
        
        // Давхар бүртгэхийг хориглох
        if ($this->has($name)) {
            throw new ContainerException(__CLASS__ . ' already contains entry named [' . $name . ']');
        }
        
        // Тодорхойлолтыг хадгална (lazy loading - одоо instance үүсгэхгүй)
        $this->definitions[$name] = $definition;
    }

    /**
     * Контейнерээс сервис устгах
     * 
     * Тодорхойлолт болон кэшлэгдсэн instance-ийг хоёуланг нь устгана.
     *
     * @param string $name
     * @return void
     */
    public function remove(string $name): void
    {
        if ($this->has($name)) {
            unset($this->definitions[$name]);
        }
        
        // Кэшлэгдсэн instance байвал устгах
        if (isset($this->instances[$name])) {
            unset($this->instances[$name]);
        }
    }
}
