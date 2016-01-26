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

    public function testThomasDorland()
    {
        $dbh = new PDO();

        $bot = new Bot($dbh);

        $navarr = \Shotbow_ChatBot_User::create(1, 'Navarr');
        $thomas = \Shotbow_ChatBot_User::create(1669321, 'Thomas_Dorland');

        $messages = $bot->process($navarr, '!startminez');

        $this->assertCount(1, $messages);
        $navarrs = $messages[0];

        $messages = $bot->process($thomas, '!startminez');

        $this->assertCount(1, $messages);
        $thomass = $messages[0];

        $this->assertNotEquals($navarrs, $thomass);
    }

    public function testAllCommandsDontError()
    {
        $dbh = new PDO();

        $bot = new Bot($dbh);

        $navarr = \Shotbow_ChatBot_User::create(1, 'Navarr');

        $commands = $bot->getCommandList();
        foreach ($commands as $command => $callable) {
            $bot->process($navarr, "!{$command}");
        }
    }
}
