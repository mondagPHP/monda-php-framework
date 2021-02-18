<?php

namespace framework\annotation\yapi;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * 控制器注解
 * @Annotation
 * @Target({"CLASS"})
 */
final class YController
{
    /**
     * @Required()
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $desc;

    public function __construct(array $values)
    {
        $this->name = $values['name'];
        $this->desc = $values['desc'];
    }
}
