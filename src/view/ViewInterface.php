<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\view;

interface ViewInterface
{
    // 初始化模板
    public function init();

    // 解析模板模板
    public function render(string $path, array $params = []): string;
}
