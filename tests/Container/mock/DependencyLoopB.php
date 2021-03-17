<?php


namespace framework\tests\Container\mock;


class DependencyLoopB
{
    public function __construct(DependencyLoopC $c)
    {}
}