<?php

namespace framework\cookie;
/**
 * Class Cookie
 * cookie 操作类
 */
class Cookie
{
    /**
     * Cookie 前缀
     * @var string
     */
    protected $prefix = 'monda_';

    /**
     * Cookie 保存时间
     * @var int
     */
    protected $expire = 0;

    /**
     * Cookie 保存路径
     * @var string
     */
    protected $path = '/';

    /**
     * Cookie 有效域名
     * @var string
     */
    protected $domain = '';

    /**
     * 是否启用安全传输
     * @var bool
     */
    protected $secure = false;

    /**
     * httponly
     * @var bool
     */
    protected $httponly = false;

    /**
     * 是否使用setcookie
     * @var bool
     */
    protected $setcookie = true;

    /**
     * Cookie constructor.
     * @param array $options
     */
    public function __construct(string $prefix = '', array $options = [])
    {
        if (!empty($this->httponly)) {
            ini_set('session.cookie_httponly', 1);
        }
        if (!empty($prefix)) {
            $this->prefix = $prefix;
        }
        if ($options) {
            foreach ($options as $key => $val) {
                isset($this->$key) && $this->$key = $val;
            }
        }
        return $this;
    }


    /**
     * 判断Cookie是否存在
     * @param   $name           Cookie 名称
     * @return  bool
     */
    public function has($name)
    {
        $name = $this->prefix . $name;
        return isset($_COOKIE[$name]);
    }

    /**
     * 设置Cookie
     * @param string $name Cookie 名称
     * @param mixed $value Cookie 值
     */
    public function set(string $name, $value, int $exprie = 3600)
    {
        $name = $this->prefix . $name;
        if (is_array($value)) {
            $value = json_encode($value);
        }
        if ($this->setcookie) {
            setcookie($name, $value, $expire, $this->path, $this->domain, $this->secure, $this->httponly);
        }
        $_COOKIE[$name] = $value;
    }

    /**
     * 获取Cookie数据
     * @param string $name Cookie 名称，为空获取所有
     * @return array|mixed|null
     */
    public function get(string $name, $default = null)
    {
        $name = $this->prefix . $name;
        if (isset($_COOKIE[$name])) {
            $value = $_COOKIE[$name];
            if (!is_null(json_decode($value))) {
                $value = json_decode($value, true);
            }
        } else {
            $value = $default;
        }
        return $value;
    }

    /**
     * 删除Cookie
     * @param $name         Cookie 名称
     */
    public function delete($name)
    {
        $name = $this->prefix . $name;
        if ($this->setcookie) {
            setcookie($name, '', $_SERVER['REQUEST_TIME'] - 3600, $this->path, $this->domain, $this->secure, $this->httponly);
        }
        unset($_COOKIE[$name]);
    }

    /**
     * 清空Cookie
     */
    public function clear()
    {
        if (empty($_COOKIE)) {
            return;
        }
        $prefix = $this->prefix;
        if ($prefix) {
            foreach ($_COOKIE as $key => $val) {
                if (0 === strpos($key, $prefix)) {
                    if ($this->setcookie) {
                        setcookie($key, '', $_SERVER['REQUEST_TIME'] - 3600, $this->path, $this->domain, $this->secure, $this->httponly);
                    }
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }

    /**
     * 永久保存Cookie
     * @param $name             Cookie 名称
     * @param string $value Cookie 值
     */
    public function forever($name, $value)
    {
        $expire = 3600 * 24 * 365;
        $this->set($name, $value, $exprie);
    }
}