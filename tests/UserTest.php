<?php
namespace Shotbow\ChatBot;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $user = \Shotbow_ChatBot_User::create(1, 'Navarr');

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('Navarr', $user->getName());

        $user = \Shotbow_ChatBot_User::create(2, 'Bob');

        $this->assertEquals(2, $user->getId());
        $this->assertEquals('Bob', $user->getName());
    }

    public function testToString()
    {
        $user = \Shotbow_ChatBot_User::create(2, 'lazertester');

        $stringified = (string)$user;

        $this->assertEquals('lazertester', $stringified);
    }
}
