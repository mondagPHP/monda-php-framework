<?php

namespace framework\vo;

/**
 * Interface VoInterface
 * @package framework\vo
 * vo接口类
 */
interface RequestVoInterface
{
    public function getRequestValidator(): string;

    public function getRequestScene(): string;
}
