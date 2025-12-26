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
 * <b>Auto-wiring:</b> Constructor-ын параметрүүдэд class type hint байвал
 * container-ээс автоматаар dependency resolve хийгдэнэ. Хэрэв user аргумент
 * өгсөн бол түүнийг ашиглана (auto-wiring-ээс давуу).
 *
 * <b>Interface Binding:</b> Interface-үүдийг implementation-уудтай холбох
 * боломжтой. Interface-ийг get() дуудахад implementation instance буцаана.
 * Auto-wiring-тэй хамт ажиллана.
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
     * Interface-үүдийг implementation-уудтай холбосон binding-үүд.
     * Interface нэр -> Implementation нэр mapping.
     *
     * @var array<string, string>
     */
    protected $bindings = [];

    /**
     * Сервисүүдийн alias-үүд.
     * Alias нэр -> Бодит сервисийн нэр mapping.
     *
     * @var array<string, string>
     */
    protected $aliases = [];

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
        // Alias байвал бодит нэрийг авах
        $aliasResolvedName = $this->resolveAlias($name);
        
        // Interface binding байвал implementation-ийг авах
        $resolvedName = $this->resolveBinding($aliasResolvedName);
        
        if (!$this->has($resolvedName)) {
            throw new NotFoundException('Entry not found: ' . $name);
        }

        // Кэшлэгдсэн instance байвал буцаана
        if (isset($this->instances[$resolvedName])) {
            $instance = $this->instances[$resolvedName];
            // Alias нэрээр ч кэшлэх
            if ($resolvedName !== $name && !isset($this->instances[$name])) {
                $this->instances[$name] = $instance;
            }
            return $instance;
        }

        // Lazy loading: зөвхөн одоо instance үүсгэнэ
        $definition = $this->definitions[$resolvedName];
        
        if (\is_callable($definition)) {
            // Callable бол container-ийг дамжуулж дуудна
            $instance = $definition($this);
        } else {
            // Класс нэр бол Reflection ашиглан instance үүсгэнэ
            $reflector = new ReflectionClass($resolvedName);
            $resolvedArgs = $this->resolveConstructorArguments($reflector, $definition);
            $instance = $reflector->newInstanceArgs($resolvedArgs);
        }

        // Кэшлэх (singleton behavior)
        $this->instances[$resolvedName] = $instance;
        
        // Interface binding эсвэл alias байвал нэрээр ч кэшлэх
        if ($resolvedName !== $name) {
            $this->instances[$name] = $instance;
        }
        
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
        // Alias байвал бодит нэрийг авах
        $resolvedName = $this->resolveAlias($name);
        
        // Шууд бүртгэлтэй эсэхийг шалгах
        if (isset($this->definitions[$resolvedName])) {
            return true;
        }
        
        // Interface binding байвал шалгах
        if (isset($this->bindings[$resolvedName])) {
            return $this->has($this->bindings[$resolvedName]);
        }
        
        return false;
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
     * Interface binding эсвэл alias байвал түүнийг ч устгана.
     *
     * @param string $name
     * @return void
     */
    public function remove(string $name): void
    {
        // Зөвхөн alias байвал alias-ийг устгах, бодит сервис хэвээр байх
        if (isset($this->aliases[$name])) {
            unset($this->aliases[$name]);
            // Alias-ийн instance кэш устгах
            if (isset($this->instances[$name])) {
                unset($this->instances[$name]);
            }
            return;
        }
        
        // Interface binding байвал зөвхөн binding-ийг устгах
        if (isset($this->bindings[$name])) {
            unset($this->bindings[$name]);
            // Interface binding-ийн instance кэш устгах
            if (isset($this->instances[$name])) {
                unset($this->instances[$name]);
            }
            return;
        }
        
        // Alias байвал бодит нэрийг авах
        $resolvedName = $this->resolveAlias($name);
        $finalName = $this->resolveBinding($resolvedName);
        
        // Бүх alias-үүдийг шалгаж устгах (энэ сервис рүү чиглэсэн)
        $this->aliases = \array_filter($this->aliases, function($target) use ($finalName) {
            return $target !== $finalName;
        }, ARRAY_FILTER_USE_BOTH);
        
        // Бодит сервис устгах
        if (isset($this->definitions[$finalName])) {
            unset($this->definitions[$finalName]);
        }
        
        // Interface binding байвал устгах
        if (isset($this->bindings[$finalName])) {
            unset($this->bindings[$finalName]);
        }
        
        // Кэшлэгдсэн instance байвал устгах
        if (isset($this->instances[$finalName])) {
            unset($this->instances[$finalName]);
        }
        if (isset($this->instances[$name])) {
            unset($this->instances[$name]);
        }
    }

    /**
     * Constructor-ын аргументуудыг auto-wiring ашиглан resolve хийх.
     * 
     * Auto-wiring: Constructor-ын параметрүүдэд class type hint байвал
     * container-ээс автоматаар авах. Хэрэв user-ийн өгсөн аргументууд байвал
     * тэдгээрийг ашиглана.
     *
     * @param ReflectionClass $reflector Reflection класс
     * @param array $userArgs Хэрэглэгчийн өгсөн аргументууд
     * @return array Resolved аргументууд
     */
    protected function resolveConstructorArguments(ReflectionClass $reflector, array $userArgs = []): array
    {
        $constructor = $reflector->getConstructor();
        
        // Constructor байхгүй бол хоосон array буцаана
        if ($constructor === null) {
            return [];
        }
        
        $parameters = $constructor->getParameters();
        $resolvedArgs = [];
        $userArgsIndex = 0;
        
        foreach ($parameters as $param) {
            $paramType = $param->getType();
            
            // Хэрэв user-ийн аргумент байвал ашиглана
            if ($userArgsIndex < \count($userArgs)) {
                $resolvedArgs[] = $userArgs[$userArgsIndex];
                $userArgsIndex++;
                continue;
            }
            
            // Auto-wiring: Class type hint байвал container-ээс авах
            if ($paramType !== null && !$paramType->isBuiltin()) {
                $typeName = $paramType->getName();
                
                // Interface binding эсвэл шууд бүртгэлтэй байвал авах
                if ($this->has($typeName)) {
                    $resolvedArgs[] = $this->get($typeName);
                    continue;
                }
            }
            
            // Optional параметр байвал default value ашиглах
            if ($param->isDefaultValueAvailable()) {
                $resolvedArgs[] = $param->getDefaultValue();
                continue;
            }
            
            // Required параметр байвал алдаа
            throw new ContainerException(
                'Cannot resolve parameter "' . $param->getName() . '" for class "' . $reflector->getName() . '". ' .
                'Parameter is required and not provided, and auto-wiring failed.'
            );
        }
        
        return $resolvedArgs;
    }

    /**
     * Interface-ийг implementation-тай холбох.
     * 
     * Interface binding: Interface-ийг implementation класстай холбох.
     * Ингэснээр interface-ийг get() дуудахад implementation instance буцаана.
     *
     * @param string $interface Interface нэр
     * @param string $implementation Implementation класс нэр
     *
     * @throws NotFoundException     Interface эсвэл implementation байхгүй бол
     * @throws ContainerException    Давхар binding хийх үед
     *
     * @return void
     */
    public function bind(string $interface, string $implementation): void
    {
        // Interface байхгүй бол алдаа
        if (!\interface_exists($interface)) {
            throw new NotFoundException($interface . ' interface does not exist');
        }
        
        // Implementation класс байхгүй бол алдаа
        if (!\class_exists($implementation)) {
            throw new NotFoundException($implementation . ' class does not exist');
        }
        
        // Implementation нь interface-ийг хэрэгжүүлж байгаа эсэхийг шалгах
        $reflector = new ReflectionClass($implementation);
        if (!$reflector->implementsInterface($interface)) {
            throw new ContainerException(
                $implementation . ' does not implement interface ' . $interface
            );
        }
        
        // Давхар binding хийхийг хориглох
        if (isset($this->bindings[$interface])) {
            throw new ContainerException(
                __CLASS__ . ' already contains binding for interface [' . $interface . ']'
            );
        }
        
        // Binding хадгална
        $this->bindings[$interface] = $implementation;
    }

    /**
     * Interface binding-ийг resolve хийх.
     * 
     * Хэрэв interface binding байвал implementation нэрийг буцаана,
     * эсрэг тохиолдолд анхны нэрийг буцаана.
     *
     * @param string $name Interface эсвэл класс нэр
     * @return string Resolved нэр
     */
    protected function resolveBinding(string $name): string
    {
        // Interface binding байвал implementation нэрийг буцаана
        if (isset($this->bindings[$name])) {
            return $this->bindings[$name];
        }
        
        return $name;
    }

    /**
     * Сервисэд alias нэр оноох.
     * 
     * Alias нь нэг сервисийг олон нэрээр авах боломжийг олгодог.
     * Ижил instance буцаана (singleton behavior).
     *
     * @param string $alias Alias нэр
     * @param string $name Бодит сервисийн нэр
     *
     * @throws NotFoundException     Сервис олдохгүй бол
     * @throws ContainerException    Давхар alias хийх үед
     *
     * @return void
     */
    public function alias(string $alias, string $name): void
    {
        // Бодит сервис байх ёстой
        $resolvedName = $this->resolveAlias($name);
        $finalName = $this->resolveBinding($resolvedName);
        
        if (!$this->has($finalName)) {
            throw new NotFoundException('Cannot create alias: service [' . $name . '] not found');
        }
        
        // Давхар alias хийхийг хориглох
        if (isset($this->aliases[$alias])) {
            throw new ContainerException(
                __CLASS__ . ' already contains alias named [' . $alias . ']'
            );
        }
        
        // Alias-ийг бодит сервис нэртэй ижил байхыг хориглох
        if ($alias === $finalName) {
            throw new ContainerException(
                'Cannot create alias: alias name [' . $alias . '] cannot be the same as service name'
            );
        }
        
        // Alias хадгална
        $this->aliases[$alias] = $finalName;
    }

    /**
     * Alias-ийг resolve хийх.
     * 
     * Хэрэв alias байвал бодит сервисийн нэрийг буцаана,
     * эсрэг тохиолдолд анхны нэрийг буцаана.
     *
     * @param string $name Alias эсвэл бодит нэр
     * @return string Resolved нэр
     */
    protected function resolveAlias(string $name): string
    {
        // Alias байвал бодит нэрийг буцаана
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }
        
        return $name;
    }
}
