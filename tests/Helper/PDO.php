<?php

namespace Shotbow\ChatBot\Test\Helper;

class PDO extends \PDO
{
    public function __construct()
    {
        // do nothing
    }

    public function prepare($statement, $driver_options = [])
    {
        $stmt = new Statement($statement);

        return $stmt;
    }

    public function query($statement, $mode = self::ATTR_DEFAULT_FETCH_MODE, $arg3 = null)
    {
        static::debugOutput(str_replace(["\r", "\n"], ' ', $statement));
        $activeUserQuery
            = <<<'MySQL'
SELECT user.user_id, user.username
FROM dark_taigachat_activity AS activity
LEFT JOIN xf_user AS user ON (user.user_id = activity.user_id)
WHERE activity.date > UNIX_TIMESTAMP()-150 AND user.visible=1
ORDER BY activity.date DESC
MySQL;
        $weeklyArrowQuery = 'SELECT url FROM weeklyarrow_published ORDER BY id DESC LIMIT 1';
        switch ($statement) {
            case $activeUserQuery:
            default:
                return new ActiveUserStatement($statement);
            case $weeklyArrowQuery:
                return new WeeklyArrowStatement($statement);
        }
    }

    public static function debugOutput($sql, $params = [])
    {
        if (defined('DEBUG')) {
            echo "[SQL] {$sql} (".implode(',', $params).')'.PHP_EOL;
        }
    }
}
