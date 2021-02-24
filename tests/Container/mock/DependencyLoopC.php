<?php


namespace framework\tests\Container\mock;


class DependencyLoopC
{
    public function __construct(DependencyLoopB $b)
    {}
}