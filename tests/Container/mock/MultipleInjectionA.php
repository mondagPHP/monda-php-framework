<?php


namespace framework\tests\Container\mock;


class MultipleInjectionA
{
    /**
     * @var MultipleInjectionB
     */
    private $injectionB;
    /**
     * @var array
     */
    private $config;

    public function __construct(MultipleInjectionB $injectionB, array $config)
    {
        $this->injectionB = $injectionB;
        $this->config = $config;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function injectionBHello(): string
    {
        return $this->injectionB->hello();
    }
}