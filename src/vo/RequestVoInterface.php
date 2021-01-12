<?php

namespace framework\vo;

/**
 * Interface VoInterface
 * @package framework\vo
 * vo接口类
 */
interface RequestVoInterface
{
    /**
     * @return array
     * $arr = [验证器类, 方法];
     */
    public function valid(): array;
}
