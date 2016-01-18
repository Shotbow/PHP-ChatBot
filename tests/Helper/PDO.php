<?php
namespace Shotbow\ChatBot\Test\Helper;

class PDO extends \PDO
{
    public function __construct()
    {
        // do nothing
    }

    public function prepare($statement, $driver_options = array())
    {
        $stmt = new Statement($statement);
        return $stmt;
    }

    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null)
    {
        static::debugOutput($statement);
    }

    public static function debugOutput($sql, $params = [])
    {
        echo "[SQL] {$sql} (".implode(',', $params).")".PHP_EOL;
    }
}
