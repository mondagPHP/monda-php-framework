<?php

namespace framework\util;

/**
 * Class Pager.
 */
class Pager
{
    /**
     * @var int 当前页
     */
    public $currentPage;

    /**
     * @var int 分页数量
     */
    public $pageSize;

    /**
     * @var int 总量
     */
    public $total;

    /**
     * @var int 总页数
     */
    public $totalPage;

    /**
     * Pager constructor.
     *
     * @param $currentPage
     * @param $pageSize
     * @param $total
     * 分页
     */
    public function __construct($currentPage, $pageSize, $total)
    {
        $this->currentPage = $currentPage;
        $this->pageSize = $pageSize;
        $this->total = $total;
        $this->totalPage = 0 != $pageSize ? ceil($total / $pageSize) : 0;
    }
}
