<?php


namespace framework\tests\Container\mock;


class MockConfig
{
    protected $config = [];

    // 扫描 config 文件夹,加入到配置的大数组
    public function init(): void
    {
        foreach (glob('tests/Container/mock/config/*.php') as $file) {
            $key = str_replace('.php', '', basename($file));
            $this->config[$key] = require $file;
        }
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     *               获取配置
     */
    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $this->config;
        if (is_array($keys)) {
            foreach ($keys as $v) {
                if (! isset($config[$v])) {
                    return $default;
                }
                $config = $config[$v];
            }
            return $config;
        }
        return $default;
    }

    /**
     * 重新配置文件.
     * @param $key
     * @param $val
     */
    public function set($key, $val): void
    {
        $keys = explode('.', $key);
        $newConfig = &$this->config;
        foreach ($keys ?? [] as $k) {
            $newConfig = &$newConfig[$k]; // 传址
        }
        $newConfig = $val;
    }
}