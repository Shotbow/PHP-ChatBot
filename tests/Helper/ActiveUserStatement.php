<?php

namespace Shotbow\ChatBot\Test\Helper;

class ActiveUserStatement extends \Shotbow\ChatBot\Test\Helper\Statement
{
    public function __construct($sql)
    {
        parent::__construct($sql);
        $this->results = [
            [
                'user_id'  => 319,
                'username' => 'Navarr',
            ],
            [
                'user_id'  => 1729,
                'username' => 'SocomX1',
            ],
            [
                'user_id'  => 134749,
                'username' => 'lazertester',
            ],
        ];
    }
}
