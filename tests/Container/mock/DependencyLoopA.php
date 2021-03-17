<?php


namespace framework\tests\Container\mock;


class DependencyLoopA
{
    public function __construct(DependencyLoopB $b)
    {}
}