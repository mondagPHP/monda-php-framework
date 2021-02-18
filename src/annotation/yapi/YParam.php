<?php
namespace framework\annotation\yapi;

use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * 参数
 * @Annotation
 * @Target({"METHOD"})
 */
final class YParam
{
    /**
     * @Required()
     * @var string
     */
    public $name;

    /**
     * @Enum({"1", "0"})
     * @var string
     */
    public $required;

    /**
     * @Enum({"text", "file"})
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $example;

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
        $this->required = $values['required'] ?? '1';
        $this->type = $values['type'] ?? 'text';
        $this->example = $values['example'] ?? '';
        $this->desc = $values['desc'] ?? '';
    }
}
