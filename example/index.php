<?php

namespace codesaur\Container\Example;

use codesaur\Container\Container;

/* 
 * DEV: v1.2021.09.10
 *
 * Энэ бол codesaur/container багцын ашиглалтыг харуулах энгийн жишээ файл.
 */

$autoload = require_once '../vendor/autoload.php';

/**
 * Class Printer
 *
 * Контейнерээс inject хийх боломжтой, текст хэвлэгч энгийн класс.
 */
class Printer
{
    /**
     * Хэвлэх текст
     * @var string
     */
    public $text;

    /**
     * @param string $arg Хэвлэх текстийн утга
     */
    public function __construct($arg)
    {
        $this->text = $arg;
    }

    /**
     * Текстийг дэлгэцэнд хэвлэх
     */
    public function print()
    {
        echo $this->text;
    }
}

/**
 * Class Calculator
 *
 * Энгийн тооны машин — хоёр тоог нэмэх жишээ.
 * Контейнерээс resolve хийж ашиглахад тохиромжтой.
 */
class Calculator
{
    /**
     * Нэмэх үйлдэл
     *
     * @param mixed $a
     * @param mixed $b
     * @return int|float
     *
     * @throws \InvalidArgumentException Тоон утга биш параметр ирвэл
     */
    public function sum($a, $b)
    {
        if (!\is_numeric($a) || !\is_numeric($b)) {
            throw new \InvalidArgumentException('Args must be numeric values!');
        }

        return $a + $b;
    }
}

// --------------------------------------------
// Контейнер ашиглах хэсэг
// --------------------------------------------

$container = new Container();

// Printer классыг параметртэйгээр бүртгэж байна
$container->set(Printer::class, [
    "<br/>That's all.<br/>Very basic container sample."
]);

// Calculator классыг параметргүйгээр бүртгэж байна
$container->set(Calculator::class);

// Контейнерээс Calculator service-ийг авч ашиглах
$a = 16;
$b = 7;

$calculator = $container->get(Calculator::class);
echo "$a + $b = " . $calculator->sum($a, $b);

// Printer сервисийг авч ажиллуулах
$container->get(Printer::class)->print();
