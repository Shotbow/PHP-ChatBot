<?php

class Shotbow_ChatBot_Bot
{
    const INFO_ID = '1587103';
    const INFO_NAME = 'Chat Bot';

    /** @var array */
    private $commands;

    /** @var array */
    private $aliases;

    /** @var PDO */
    private $dbh;

    /** @var string */
    private $postUrl;

    public function __construct(PDO $databaseHandle, $postUrl)
    {
        $this->dbh     = $databaseHandle;
        $this->postUrl = $postUrl;
    }

    public function process(Shotbow_ChatBot_User $sender, $message)
    {
        if (substr($message, 0, 1) == '!') {
            // This might be a command.
            $separate  = explode(' ', $message, 2);
            $command   = substr($separate[0], 1);
            $arguments = isset( $separate[1] ) ? $separate[1] : null;
            if ($this->commandExists($command) || $this->aliasExists($command)) {
                // do rate limiting
                $callable = $this->getCommandCallable($command);
                $callable($sender, $arguments);
            }
        }
    }

    protected function postMessage($message, $name = null)
    {
        $name = is_null($name) ? static::INFO_NAME : $name;
        $user = Shotbow_ChatBot_User::create(static::INFO_ID, $name);

        // Post to DB for website
        $stmt = $this->dbh->prepare(
            'INSERT INTO dark_taigachat (user_id,username,`date`,message,activity) VALUES (?,?,?,?,0)'
        );
        $stmt->execute([static::INFO_ID, $user->getName(), time(), $message]);

        $this->postToInternal($message, $user);
    }

    private function postToInternal($message, Shotbow_ChatBot_User $user, $channel = '#shoutbox')
    {
        $internalFormatted = $message;
        $internalFormatted = preg_replace('#\[url=([^\]]+)\]([^\[]+)\[/url\]#', '<$1|$2>', $internalFormatted);

        $payload = json_encode(
            array(
                'username' => $user->getName(),
                'icon_url' => 'https://shotbow.net/forum/mobiquo/avatar.php?user_id=' . $user->getId(),
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
        if (!isset( $this->commands )) {
            $this->commands = [
                //'help'     => [$this, 'command_help'],
                'commands' => [$this, 'command_commands'],
                'rules'    => [$this, 'command_rules'],
                'banned'   => [$this, 'command_banned'],
                'xp'       => [$this, 'command_xp'],
                'report'   => [$this, 'command_report'],
                'staff'    => [$this, 'command_staff'],
                'social'   => [$this, 'command_social'],
                'about'    => [$this, 'command_about'],
                'bug'      => [$this, 'command_bug'],
                'ts'       => [$this, 'command_teamspeak'],
                'ip'       => [$this, 'command_ip'],
            ];
        }
        return $this->commands;
    }

    protected function getCommandAliases()
    {
        if (!isset( $this->aliases )) {
            $this->aliases = [
                // Social Services
                'twitter'    => 'social',
                'facebook'   => 'social',
                'youtube'    => 'social',
                'googleplus' => 'social',
                'gplus'      => 'social',
                'youku'      => 'social',
                'playerme'   => 'social',
                'instagram'  => 'social',
                'tumblr'     => 'social',

                'source'    => 'about',
                'bugs'      => 'bug',
                'bugreport' => 'bug',
                'address'   => 'ip',
                'teamspeak' => 'ts',
                'mumble'    => 'ts',
            ];
        }
        return $this->aliases;
    }

    protected function commandExists($command)
    {
        $commands = $this->getCommandList();

        return isset( $commands[$command] );
    }

    protected function aliasExists($command)
    {
        $aliases = $this->getCommandAliases();
        return isset( $aliases[$command] ) && isset( $commands[$aliases[$command]] );
    }

    /**
     * @param $command
     *
     * @return callable
     */
    protected function getCommandCallable($command)
    {
        $commands = $this->getCommandList();
        $aliases  = $this->getCommandAliases();
        if ($this->commandExists($command)) {
            return $commands[$command];
        }
        if ($this->aliasExists($command)) {
            return $commands[$aliases[$command]];
        }
        return [$this, 'emptyCallback'];
    }

    protected function emptyCallback($sender, $arguments)
    {

    }

    protected function command_commands(Shotbow_ChatBot_User $sender, $arguments)
    {
        $commands = $this->getCommandList();
        $text     = 'All Available Commands: ';
        $commands = array_keys($commands);
        $commands = array_map(
            function ($value) {
                return '!' . $value;
            },
            $commands
        );
        $text .= implode(', ', $commands);
        $this->postMessage($text);
    }

    protected function command_banned(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message = 'We do not discuss bans in the chatroom.  Please [url=https://shotbow.net/forum/threads/23560/]Post an Appeal[/url].  It is the fastest way to get your ban handled.';
        $this->postMessage($message);
    }

    protected function command_rules(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message = 'Please [url=https://shotbow.net/forum/p/rules/]Read our Rules[/url].';
        $this->postMessage($message);
    }

    protected function command_xp(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message = 'We have a special xp code for people that ask!  Try IASKEDFORXP';
        $this->postMessage($message);
    }

    protected function command_report(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message
            = 'To report a malicious player, follow [url=https://shotbow.net/forum/threads/167314/]our Report a Player instructions[/url]';
        $this->postMessage($message);
    }

    protected function command_staff(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message = 'Our Wiki Moderators maintain an unofficial [url=https://shotbow.net/forum/wiki/shotbow-staff]list of staff[/url]';
        $this->postMessage($message);
    }

    protected function command_social(Shotbow_ChatBot_User $sender, $arguments)
    {
        $profiles = [
            'Facebook' => 'https://facebook.com/TheShotbowNetwork',
            'Twitter' => 'https://twitter.com/ShotbowNetwork',
            'Google+' => 'https://google.com/+TheShotbowNetwork',
            'YouTube' => 'https://gaming.youtube.com/user/ShotBowNetwork',
            'Player.me' => 'https://player.me/?invite=shotbow',
            'Instagram' => 'https://instagram.com/shotbownetworkmc/',
            'Tumblr' => 'http://tumblr.shotbow.net/',
            'Youku' => 'http://i.youku.com/shotbow',
        ];

        $urlProfiles = [];
        foreach ($profiles as $name => $link) {
            $urlProfiles[] = "[URL={$link}]{$name}[/URL]";
        }
        $lastProfile = array_splice($urlProfiles, -1);
        $profileString = implode(', ', $urlProfiles);
        $profileString.= ', or '.$lastProfile[0];

        $message = "Follow us online at {$profileString}.";
        $this->postMessage($message);
    }

    protected function command_about(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message = "I'm an Open-Sourced Bot here to help you!  You can view my code and contribute to me [url=https://github.com/shotbow/chatbot]on github[/url].";
        $this->postMessage($message);
    }

    protected function command_bug(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message = "Help keep our games stable by [url=https://shotbow.net/forum/link-forums/report-a-bug.670/]Reporting Bugs[/url].";
        $this->postMessage($message);
    }

    protected function command_teamspeak(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message = "You can [url=https://shotbow.net/forum/wiki/shotbow-teamspeak/]Connect to our Teamspeak Server[/url] at ts.shotbow.net";
        $this->postMessage($message);
    }

    protected function command_ip(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message = "Connect to us on US.SHOTBOW.NET or EU.SHOTBOW.NET.  Having Trouble?  [url=https://shotbow.net/forum/threads/having-trouble-connecting-to-us-or-eu-read-this.229762/]Try these steps[/url].";
        $this->postMessage($message);
    }
}
