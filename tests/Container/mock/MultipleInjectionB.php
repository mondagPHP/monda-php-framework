<?php


namespace framework\tests\Container\mock;


class MultipleInjectionB
{
    /**
     * @var MultipleInjectionC
     */
    private $injectionC;
    /**
     * @var string
     */
    private $name;

    public function __construct(MultipleInjectionC $injectionC, string $name = 'zhangsan')
    {
        $this->injectionC = $injectionC;
        $this->name = $name;
    }

    public function injectionCHello(): string
    {
        return $this->injectionC->hello();
    }

    public function hello(): string
    {
        return 'in injectionB, Hello, ' . $this->name;
    }
}