<?php

class Shotbow_ChatBot
{
    const INFO_ID   = '1587103';
    const INFO_NAME = 'Chat Bot';

    private $commands;
    private $dbh;
    private $postUrl;

    public function __construct(PDO $databaseHandle, $postUrl)
    {
        $this->dbh = $databaseHandle;
        $this->postUrl = $postUrl;
    }

    public function process($sender, $message)
    {
        $sender = trim($sender);
        if (substr($message, 0, 1) == '!') {
            // This might be a command.
            $separate  = explode(' ', $message, 2);
            $command   = substr($separate[0], 1);
            $arguments = isset($separate[1]) ? $separate[1] : null;
            if ($this->commandExists($command)) {
                // do rate limiting
                $callable = $this->getCommandCallable($command);
                $callable($sender, $arguments);
            }
        }
    }

    protected function postMessage($message, $name = null)
    {
        $name = is_null($name) ? static::INFO_NAME : $name;

        // Post to DB for website
        $stmt = $this->dbh->prepare(
            'INSERT INTO dark_taigachat (user_id,username,`date`,message,activity) VALUES (?,?,?,?,0)'
        );
        $stmt->execute([static::INFO_ID, $name, time(), $message]);

        $this->postToInternal($message, static::INFO_ID, $name);
    }

    private function postToInternal($message, $userId, $username, $channel = '#shoutbox')
    {
        $internalFormatted = $message;
        $internalFormatted = preg_replace('#\[url=([^\]]+)\]([^\[]+)\[/url\]#', '<$1|$2>', $internalFormatted);

        $payload = json_encode(
            array(
                'username' => $username,
                'icon_url' => 'https://shotbow.net/forum/mobiquo/avatar.php?user_id=' . $userId,
                'text'     => $internalFormatted,
                'channel'  => $channel,
            )
        );

        $ch = @curl_init($this->postUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'payload=' . $payload);
        curl_exec($ch);
    }

    protected function getCommandList()
    {
        if (!isset($this->commands)) {
            $this->commands = [
                //'help'     => [$this, 'command_help'],
                'commands' => [$this, 'command_commands'],
                'rules'    => [$this, 'command_rules'],
                'banned'   => [$this, 'command_banned'],
                'xp'       => [$this, 'command_xp'],
            ];
        }
        return $this->commands;
    }

    protected function commandExists($command)
    {
        $commands = $this->getCommandList();
        return isset($commands[$command]);
    }

    protected function getCommandCallable($command)
    {
        $commands      = $this->getCommandList();
        return $this->commandExists($command) ? $commands[$command] : [$this, 'emptyCallback'];
    }

    protected function emptyCallback($sender, $arguments)
    {

    }

    protected function command_commands($sender, $arguments)
    {
        $commands = $this->getCommandList();
        $text = 'All Available Commands: ';
        $commands = array_keys($commands);
        $commands = array_map(function($value) { return '!'.$value; }, $commands);
        $text.= implode(', ',$commands);
        $this->postMessage($text);
    }

    protected function command_banned($sender, $arguments)
    {
        $to = $sender;
        if (!is_null($arguments)) {
            $to = $arguments;
        }

        $message = 'We do not discuss bans in the chatroom.  Please [url=https://shotbow.net/forum/threads/23560/]Post an Appeal[/url].  It is the fastest way to get your ban handled.';
        $this->postMessage($message);
    }

    protected function command_rules($sender, $arguments)
    {
        $to = $sender;
        if (!is_null($arguments)) {
            $to = $arguments;
        }


        $message = 'Please [url=https://shotbow.net/forum/p/rules/]Read our Rules[/url].';
        $this->postMessage($message);
    }

    protected function command_xp($sender, $arguments)
    {
        $message = 'We have a special xp code for people that ask!  Try IASKEDFORXP';
        $this->postMessage($message);
    }
}
