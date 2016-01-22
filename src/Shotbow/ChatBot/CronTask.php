<?php

class Shotbow_ChatBot_CronTask
{
    private $minute;
    private $hour;
    private $dayOfMonth;
    private $month;
    private $dayOfWeek;
    private $callable;

    private function __construct($minute, $hour, $dayOfMonth, $month, $dayOfWeek, $callable)
    {
        $this->minute = $minute;
        $this->hour = $hour;
        $this->dayOfMonth = $dayOfMonth;
        $this->month = $month;
        $this->dayOfWeek = $dayOfWeek;
        $this->callable = $callable;
    }

    /**
     * @param int|string $minute
     * @param int|string $hour
     * @param int|string $dayOfMonth
     * @param int|string $month
     * @param int|string $dayOfWeek
     * @param callable $callable
     * @return Shotbow_ChatBot_CronTask
     */
    public static function create($minute, $hour, $dayOfMonth, $month, $dayOfWeek, $callable)
    {
        return new self($minute, $hour, $dayOfMonth, $month, $dayOfWeek, $callable);
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @param int $minute
     * @param int $hour
     * @param int $dayOfMonth
     * @param int $month
     * @param int $dayOfWeek
     *
     * @return boolean
     */
    public function shouldRunOn($minute, $hour, $dayOfMonth, $month, $dayOfWeek)
    {
        // TODO
    }

    /**
     * @param DateTime $time
     *
     * @return boolean
     */
    public function shouldRunOnDateTime(DateTime $time)
    {
        // TODO
    }
}
