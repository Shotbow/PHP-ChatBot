<?php

namespace Shotbow\ChatBot;

use Shotbow\ChatBot\Test\Helper\Bot;
use Shotbow\ChatBot\Test\Helper\PDO;

class BotTest extends \PHPUnit_Framework_TestCase
{
    public function testAbout()
    {
        $dbh = new PDO();

        $bot = new Bot($dbh);

        $user = \Shotbow_ChatBot_User::create(1, 'Navarr');

        $messages = $bot->process($user, '!about');

        $this->assertCount(1, $messages);
    }
}
