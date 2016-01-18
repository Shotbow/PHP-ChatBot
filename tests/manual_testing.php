<?php

define('DEBUG', true);

require_once '../vendor/autoload.php';

$user = Shotbow_ChatBot_User::create(1, 'Navarr');

$pdo = new \Shotbow\ChatBot\Test\Helper\PDO();

$bot = new \Shotbow\ChatBot\Test\Helper\Bot($pdo);

$bot->process($user, '!activeusers');
