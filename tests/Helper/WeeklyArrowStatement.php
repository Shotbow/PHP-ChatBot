<?php

namespace Shotbow\ChatBot\Test\Helper;

class WeeklyArrowStatement extends \Shotbow\ChatBot\Test\Helper\Statement
{
    public function __construct($sql)
    {
        parent::__construct($sql);
        $this->results = [
            [
                'url' => 'https://www.google.com/',
            ],
        ];
    }
}
