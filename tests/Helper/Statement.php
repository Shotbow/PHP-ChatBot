<?php
namespace Shotbow\ChatBot\Test\Helper;

class Statement extends \PDOStatement
{
    private $sql;

    public function __construct($sql)
    {
        $this->sql = $sql;
    }

    public function execute($input_parameters = null)
    {
        PDO::debugOutput($this->sql, $input_parameters);
    }
}
