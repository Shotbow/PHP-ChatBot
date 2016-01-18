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

    public function testUsersInChat()
    {
        $dbh = new PDO();

        $bot = new Bot($dbh);

        $user = \Shotbow_ChatBot_User::create(1, 'Navarr');

        $class = new \ReflectionClass($bot);
        $method = $class->getMethod('getUsersInChat');
        $method->setAccessible(true);

        $users = $method->invoke($bot);

        $this->assertEquals(true, is_array($users));
        $this->assertInstanceOf(\Shotbow_ChatBot_User::class, $users[0]);
    }
}
