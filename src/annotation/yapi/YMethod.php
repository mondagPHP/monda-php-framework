<?php

namespace framework\annotation\yapi;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * 方法
 * @Annotation
 * @Target({"METHOD"})
 */
final class YMethod
{
    /**
     * @Required()
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $desc;

    /**
     * YMethod constructor.
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->name = $values['name'];
        $this->method = $values['method'] ?? 'get';
        $this->desc = $values['desc'] ?? '';
    }
}
