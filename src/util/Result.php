<?php

namespace framework\util;

use framework\string\StringUtils;

/**
 * Class JsonResponse.
 */
class Result
{
    /**
     * @var bool 是否true false
     */
    private $success;

    /**
     * @var string 状态码
     */
    private $code;

    /**
     * @var string 返回信息
     */
    private $message;

    /**
     * @var object 对象
     */
    private $data;

    /**
     * @var object 对象
     */
    private $extra;

    /**
     * @var Pager 分页
     */
    private $pager;

    /**
     * Result constructor.
     */
    private function __construct()
    {
    }

    /**
     * 转换字符串.
     *
     * @return string
     */
    public function __toString(): string
    {
        $array = [
            'code' => $this->code,
            'success' => $this->success,
            'message' => $this->message,
        ];
        //设置数据
        if (! is_null($this->data)) {
            $array['data'] = $this->data;
        }
        //设置额外数据
        if (! is_null($this->extra)) {
            $array['extra'] = $this->extra;
        }
        //分页
        if (! is_null($this->pager)) {
            $array['pager'] = $this->pager;
        }
        return StringUtils::jsonEncode($array);
    }

    /**
     * @return Result
     *                设置成功
     */
    public static function ok(): self
    {
        $result = new self();
        $result->isSuccess(true)->code(ResultCode::SUCCESS['code'])->message(ResultCode::SUCCESS['message']);
        return $result;
    }

    /**
     * @return Result
     *                错误
     */
    public static function error(): self
    {
        $result = new self();
        $result->isSuccess(false)->code(ResultCode::ERROR['code'])->message(ResultCode::ERROR['message']);
        return $result;
    }

    /**
     * @param $page
     * @param $pageSize
     * @param $total
     * @param $data
     * @param array $extra
     * @return Result
     *                分页
     */
    public static function pager($page, $pageSize, $total, $data, $extra = []): self
    {
        $result = new self();
        $result->isSuccess(true)->code(ResultCode::SUCCESS['code'])->message(ResultCode::SUCCESS['message'])->data($data)->extra($extra)->setPager(new Pager($page, $pageSize, $total));

        return $result;
    }

    /**
     * @param $success
     *
     * @return $this
     *               设置success
     */
    public function isSuccess($success): self
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @param $data
     *
     * @return $this
     *               设置数据
     */
    public function data($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $message
     *
     * @return $this
     *               设置信息
     */
    public function message($message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param Pager $pager
     * @return $this
     *               设置分页
     */
    public function setPager(Pager $pager): self
    {
        $this->pager = $pager;

        return $this;
    }

    /**
     * @param $code
     *
     * @return $this
     *               设置状态码
     */
    public function code($code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param $extra
     *
     * @return $this
     *               设置额外数据
     */
    public function extra($extra): self
    {
        $this->extra = $extra;

        return $this;
    }
}
