<?php


namespace framework\tests\Container\mock;


class MultipleInjectionC
{
    public function __construct()
    {
    }

    public function hello(): string
    {
        return 'in injectionC, Hello';
    }
}