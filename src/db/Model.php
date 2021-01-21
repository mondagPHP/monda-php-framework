<?php

namespace framework\db;

/**
 * Class Model
 * @package core\db
 * 模型
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $connection = 'default';

    /**
     * Model constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        Connection::fireConnection();
        parent::__construct($attributes);
    }
}
