<?php

namespace Shotbow\ChatBot\Test\Helper;

class Statement extends \PDOStatement
{
    protected $sql;
    protected $results = [];

    public function __construct($sql)
    {
        $this->sql = $sql;
    }

    public function execute($input_parameters = null)
    {
        PDO::debugOutput($this->sql, $input_parameters);
    }

    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = 'array()')
    {
        return $this->results;
    }

    public function fetchColumn($column_number = 0)
    {
        $first = $this->results[0];
        $vals = array_values($first);

        return $vals[0];
    }
}
