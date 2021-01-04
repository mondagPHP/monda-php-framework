<?php

namespace framework\view;

use framework\Container;

/**
 * Class frameworkView
 * @package core\view
 */
class HerosphpView implements ViewInterface
{
    protected $template;

    public function init(): void
    {
        $config = Container::getContainer()->get('config')->get('view'); // 获取配置
        // 设置视图路径 和 缓存路径
        $this->template = new HerosphpTemplate($config);
    }

    /**
     * @param string $path
     * @param array $params
     * @return mixed
     * 渲染模板
     */
    public function render(string $path, array $params = []): string
    {
        return $this->template->render($path, $params);
    }
}
