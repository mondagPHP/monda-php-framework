<?php

namespace framework\tests\route;

use PHPUnit\Framework\TestCase;

class RouteMapTest extends TestCase
{
    private $mapRules = [];

    public function testEmptyRouteMap(): void
    {
        $this->mapRules = [];
        $url = '/admin/index/index';
        $url2 = '/admin/index/index?p=as';
        $this->assertEquals($url, $this->handleRouteMap($url));
        $this->assertEquals($url2, $this->handleRouteMap($url2));

    }

    public function testRouteMap(): void
    {
        $this->mapRules = [
            '^\/admin$' => '/admin/index/index',
            '^\/poster-([\d]+)$' => '/poster/poster/detail?id=$1'
        ];
        $this->assertEquals('/admin/index/index', $this->handleRouteMap('/admin'));
        $this->assertEquals('/admin/index/index', $this->handleRouteMap('/AdMin'));
        $this->assertEquals('/admin/index/home', $this->handleRouteMap('/admin/index/home'));
        $this->assertEquals('/admin/', $this->handleRouteMap('/admin/'));
        $this->assertEquals('/admin/n', $this->handleRouteMap('/admin/n'));
        $this->assertEquals('/poster-a', $this->handleRouteMap('/poster-a'));
        $this->assertEquals('/admin/poster-2', $this->handleRouteMap('/admin/poster-2'));
        $this->assertEquals('/poster/poster/detail?id=2', $this->handleRouteMap('/poster-2'));
        $this->assertEquals('/admin/login/login', $this->handleRouteMap('/admin/login/login'));
    }

    public function handleRouteMap($url)
    {
        $mapRules = [];
        foreach ($this->mapRules as $key => $value) {
            $mapRules['/' . $key . '/i'] = $value;
        }
        return preg_replace(array_keys($mapRules), array_values($mapRules), $url);
    }
}
