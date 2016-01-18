<?php
namespace Shotbow\ChatBot\Test\Helper;

class Bot extends \Shotbow_ChatBot_Bot
{
    public function process(\Shotbow_ChatBot_User $sender, $message)
    {
        static::outputDebug($message, $sender->getName());
        parent::process($sender, $message);
    }

    public static function outputDebug($message, $name)
    {
        echo "[MSG] <{$name}> {$message}".PHP_EOL;
    }

    protected function postMessage($message, $name = null)
    {
        parent::postMessage($message, $name);
        $name = is_null($name) ? static::INFO_NAME : $name;
        static::outputDebug($message, $name);
    }

    protected function postAction($action, $name = null)
    {
        parent::postAction($action, $name);
        $name = is_null($name) ? static::INFO_NAME : $name;
        static::outputDebug("/me {$action}", $name);
    }
}
