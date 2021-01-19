<?php

namespace framework\db;

/**
 * Class Model
 * @package core\db
 * 模型
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $connection;

    /**
     * Model constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        //默认是default
        if (is_null($this->connection)) {
            $this->connection = 'default';
        }
        Connection::fireConnection();
        parent::__construct($attributes);
    }
}
