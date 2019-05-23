<?php

/**
 * Interface ApplicationInterface
 * @author liu,jian <coder.keda@gmail.com>
 *
 * 系统组件 <不可改名>
 * @property \Mix\Log\Logger $log
 * @property \Mix\Console\Error|\Mix\Http\Error|\Mix\WebSocket\Error $error
 *
 * 自定义组件
 * @property \Mix\Database\PDOConnection|Mix\Database\MasterSlave\PDOConnection $db
 * @property \Mix\Redis\RedisConnection $redis
 * @property \Mix\Database\Pool\ConnectionPool $dbPool
 * @property \Mix\Redis\Pool\ConnectionPool $redisPool
 * @property \Mix\Cache\Cache $cache
 */
interface ApplicationInterface
{
}
