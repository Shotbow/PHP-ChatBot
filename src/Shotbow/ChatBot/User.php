<?php

class Shotbow_ChatBot_User
{
    private $xenforoId;
    private $xenforoName;

    private function __construct($id, $name)
    {
        $this->xenforoId   = $id;
        $this->xenforoName = trim($name);
    }

    public function __toString()
    {
        return $this->getName();
    }

    public static function create($xenforoId, $xenforoName)
    {
        return new static($xenforoId, $xenforoName);
    }

    public static function createFromXenforoUserinfo($visitor)
    {
        return new static($visitor['user_id'], $visitor['username']);
    }

    public function getId()
    {
        return $this->xenforoId;
    }

    public function getName()
    {
        return $this->xenforoName;
    }
}
