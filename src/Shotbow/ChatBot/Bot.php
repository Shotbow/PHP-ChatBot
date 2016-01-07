<?php

class Shotbow_ChatBot_Bot
{
    const INFO_ID = '1587103';
    const INFO_NAME = 'Chat Bot';

    /** @var array */
    private $commands;

    /** @var array */
    private $hiddenCommands;

    /** @var array */
    private $aliases;

    /** @var PDO */
    private $dbh;

    /** @var string */
    private $postUrl;

    public function __construct(PDO $databaseHandle, $postUrl)
    {
        $this->dbh = $databaseHandle;
        $this->postUrl = $postUrl;
    }

    public function process(Shotbow_ChatBot_User $sender, $message)
    {
        if (substr($message, 0, 1) == '!') {
            // This might be a command.
            $separate = explode(' ', $message, 2);
            $command = strtolower(substr($separate[0], 1));
            $arguments = isset($separate[1]) ? $separate[1] : null;
            if ($this->commandNameExists($command)) {
                // do rate limiting
                $callable = $this->getCommandCallable($command);
                $callable($sender, $arguments);
            }
        }
    }

    protected function commandNameExists($command)
    {
        return $this->commandExists($command) || $this->hiddenCommandExists($command) || $this->aliasExists($command);
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
        $internalFormatted = preg_replace('#\[url=([^\]]+)\]([^\[]+)\[/url\]#i', '<$1|$2>', $internalFormatted);

        $payload = json_encode(
            [
                'username' => $user->getName(),
                'icon_url' => 'https://shotbow.net/forum/mobiquo/avatar.php?user_id='.$user->getId(),
                'text'     => $internalFormatted,
                'channel'  => $channel,
            ]
        );

        $ch = @curl_init($this->postUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'payload='.$payload);
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
                'report'   => [$this, 'command_report'],
                'staff'    => [$this, 'command_staff'],
                'social'   => [$this, 'command_social'],
                'about'    => [$this, 'command_about'],
                'bug'      => [$this, 'command_bug'],
                'ts'       => [$this, 'command_teamspeak'],
                'ip'       => [$this, 'command_ip'],
                'vote'     => [$this, 'command_vote'],
                'mcstatus' => [$this, 'command_mcstatus'],
            ];
        }

        return $this->commands;
    }

    protected function getHiddenCommandList()
    {
        if (!isset($this->hiddenCommands)) {
            $this->hiddenCommands = [
                'createminezevent' => [$this, 'command_createMineZEvent'],
            ];
        }

        return $this->hiddenCommands;
    }

    protected function getCommandAliases()
    {
        if (!isset($this->aliases)) {
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

        return isset($commands[$command]);
    }

    protected function hiddenCommandExists($command)
    {
        $commands = $this->getHiddenCommandList();

        return isset($commands[$command]);
    }

    protected function resolveAlias($alias)
    {
        $aliases = $this->getCommandAliases();
        if (!isset($aliases[$alias])) {
            return;
        }

        return $aliases[$alias];
    }

    protected function aliasExists($command)
    {
        $command = $this->resolveAlias($command);
        $commands = $this->getCommandList();
        $hiddenCommands = $this->getHiddenCommandList();

        return !is_null($command) && (isset($commands[$command]) || isset($hiddenCommands[$command]));
    }

    protected function getAliasCallable($command)
    {
        $commands = $this->getCommandList();
        $hiddenCommands = $this->getHiddenCommandList();

        $trueCommand = $this->resolveAlias($command);

        if ($this->commandExists($trueCommand)) {
            return $this->getCallableFromCommandArray($commands, $trueCommand);
        }
        if ($this->hiddenCommandExists($trueCommand)) {
            return $this->getCallableFromCommandArray($hiddenCommands, $trueCommand);
        }
    }

    /**
     * @param $command
     *
     * @return callable
     */
    protected function getCommandCallable($command)
    {
        $commands = $this->getCommandList();
        $hiddenCommands = $this->getHiddenCommandList();
        if ($this->commandExists($command)) {
            return $this->getCallableFromCommandArray($commands, $command);
        }
        if ($this->hiddenCommandExists($command)) {
            return $this->getCallableFromCommandArray($hiddenCommands, $command);
        }
        if ($this->aliasExists($command)) {
            return $this->getCommandCallable($this->resolveAlias($command));
        }

        return [$this, 'emptyCallback'];
    }

    protected function getCallableFromCommandArray(array $array, $command)
    {
        return $array[$command];
    }

    protected function emptyCallback($sender, $arguments)
    {
    }

    protected function command_commands(Shotbow_ChatBot_User $sender, $arguments)
    {
        $commands = $this->getCommandList();
        $text = 'All Available Commands: ';
        $commands = array_keys($commands);
        $commands = array_map(
            function ($value) {
                return '!'.$value;
            },
            $commands
        );
        $text .= implode(', ', $commands);
        $this->postMessage($text);
    }

    protected function command_banned(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message
            = 'We do not discuss bans in the chatroom.  Please [url=https://shotbow.net/forum/threads/23560/]Post an Appeal[/url].  It is the fastest way to get your ban handled.';
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
            = 'To report a malicious player, follow [url=https://shotbow.net/forum/threads/167314/]our Report a Player instructions[/url].  Are they in game right now?  Type /report <name> to report them to our currently active staff!';
        $this->postMessage($message);
    }

    protected function command_staff(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message
            = 'Our wiki contains an [url=https://shotbow.net/forum/wiki/shotbow-staff]official list of staff[/url]';
        $this->postMessage($message);
    }

    protected function command_social(Shotbow_ChatBot_User $sender, $arguments)
    {
        $profiles = [
            'Facebook'  => 'https://facebook.com/TheShotbowNetwork',
            'Twitter'   => 'https://twitter.com/ShotbowNetwork',
            'Google+'   => 'https://google.com/+TheShotbowNetwork',
            'YouTube'   => 'https://gaming.youtube.com/user/ShotBowNetwork',
            'Player.me' => 'https://player.me/?invite=shotbow',
            'Instagram' => 'https://instagram.com/shotbownetworkmc/',
            'Tumblr'    => 'http://tumblr.shotbow.net/',
            'Youku'     => 'http://i.youku.com/shotbow',
        ];

        $urlProfiles = [];
        foreach ($profiles as $name => $link) {
            $urlProfiles[] = "[URL={$link}]{$name}[/URL]";
        }
        $lastProfile = array_splice($urlProfiles, -1);
        $profileString = implode(', ', $urlProfiles);
        $profileString .= ', or '.$lastProfile[0];

        $message = "Follow us online at {$profileString}.";
        $this->postMessage($message);
    }

    protected function command_vote(Shotbow_ChatBot_User $sender, $arguments)
    {
        $votes = [
            'Planet Minecraft'     => 'http://www.planetminecraft.com/server/minez-1398788/',
            'Minecraft Forum'      => 'http://minecraftforum.net/servers/160-shotbow',
            'MinecraftServers.org' => 'http://minecraftservers.org/server/267066',
        ];

        $urlVotes = [];
        foreach ($votes as $name => $link) {
            $urlVotes[] = "[URL={$link}]{$name}[/URL]";
        }
        $lastProfile = array_splice($urlVotes, -1);
        $profileString = implode(', ', $urlVotes);
        $profileString .= ', and '.$lastProfile[0];

        $message = "Vote for our network on {$profileString}.";

        $this->postMessage($message);
    }

    protected function command_about(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message
            = "I'm an Open-Sourced Bot here to help you!  You can view my code and contribute to me [url=https://github.com/shotbow/chatbot]on github[/url].";
        $this->postMessage($message);
    }

    protected function command_bug(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message
            = 'Help keep our games stable by [url=https://shotbow.net/forum/link-forums/report-a-bug.670/]Reporting Bugs[/url].';
        $this->postMessage($message);
    }

    protected function command_teamspeak(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message
            = 'You can [url=https://shotbow.net/forum/wiki/shotbow-teamspeak/]Connect to our Teamspeak Server[/url] at ts.shotbow.net';
        $this->postMessage($message);
    }

    protected function command_ip(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message
            = 'Connect to us on US.SHOTBOW.NET or EU.SHOTBOW.NET.  Having Trouble?  [url=https://shotbow.net/forum/threads/having-trouble-connecting-to-us-or-eu-read-this.229762/]Try these steps[/url].';
        $this->postMessage($message);
    }

    protected function command_mcstatus(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message = "Sometimes it's Mojang.  [url=http://xpaw.ru/mcstatus/]Have you checked?[/url]";
        $this->postMessage($message);
    }

    protected function command_createMineZEvent(Shotbow_ChatBot_User $sender, $arguments)
    {
        $message = "No.  That's not how this works.  Staff run events in their spare time.";
        $this->postMessage($message);
    }
}
