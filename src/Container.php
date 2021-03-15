<?php declare(strict_types=1);

namespace codesaur\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface 
{
    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        throw new \Exception(__CLASS__ . ':' . __FUNCTION__ . ' Not implemented');
    }
    
    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        throw new \Exception(__CLASS__ . ':' . __FUNCTION__ . ' Not implemented');
    }
}
