<?php

namespace codesaur\Container\Example;

use InvalidArgumentException;

use codesaur\Container\Container;

/* DEV: v1.2021.09.10
 * 
 * This is an example script!
 */

$autoload = require_once '../vendor/autoload.php';

class Printer
{    
    public $text;
    
    function __construct($arg)
    {        
        $this->text = $arg;
    }
    
    public function print()
    {
        echo $this->text;
    }
}

class Calculator
{    
    public function sum($a, $b)
    {
        if (!is_numeric($a)
            || !is_numeric($b)
        ) {
            throw new InvalidArgumentException('Args must be numeric values!');
        }
        return $a + $b;
    }
}

$container = new Container();
$container->set(Printer::class, array('<br/>That\'s all.<br/>Very basic container sample.'));
$container->set(Calculator::class);

$a = 16;
$b = 7;
$calculator = $container->get(Calculator::class);
echo "$a + $b = " . $calculator->sum($a, $b);

$container->get(Printer::class)->print();
