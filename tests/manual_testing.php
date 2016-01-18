<?php

require_once '../vendor/autoload.php';

$user = Shotbow_ChatBot_User::create(1, 'Navarr');

$pdo = new \Shotbow\ChatBot\Test\Helper\PDO();

$bot = new Shotbow_ChatBot_Bot($pdo);

$bot->process($user, '!activeusers');
