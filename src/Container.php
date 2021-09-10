<?php

namespace codesaur\Container;

use ReflectionClass;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $entries = array();

    /**
     * {@inheritdoc}
     */
    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new NotFoundException('Entry not found: ' . $name);
        }
        
        return $this->entries[$name];
    }
    
    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return isset($this->entries[$name]);
    }
    
    public function set(string $name, array $args = [])
    {
        if (!class_exists($name)) {
            throw new NotFoundException($name . ' class does not exist');
        } elseif ($this->has($name)) {
            throw new ContainerException(__CLASS__ . ' already contains entry named [' . $name . ']');
        }
        
        $reflector = new ReflectionClass($name);
        $this->entries[$name] = $reflector->newInstanceArgs($args);
    }
}
