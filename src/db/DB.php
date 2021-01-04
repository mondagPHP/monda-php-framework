<?php

namespace framework\db;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Query\Builder;

/**
 * Class DB
 * @package core\db
 */
class DB
{
    /**
     * @param $table
     * @param string $connection
     * @return Builder
     */
    public static function table($table, $connection = 'default'): Builder
    {
        Connection::fireConnection($connection);
        return Manager::table($table, $connection);
    }
}
