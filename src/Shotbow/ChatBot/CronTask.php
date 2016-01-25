<?php

use Cron\CronExpression;

class Shotbow_ChatBot_CronTask
{
    private $expr;
    private $callable;

    private function __construct(CronExpression $expression, $callable)
    {
        $this->expr = $expression;
        $this->callable = $callable;
    }

    /**
     * @param CronExpression $expression
     * @param callable       $callable
     *
     * @return Shotbow_ChatBot_CronTask
     */
    public static function create(CronExpression $expression, $callable)
    {
        return new self($expression, $callable);
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Determine if the cron is due to run based on the current date or a
     * specific date.  This method assumes that the current number of
     * seconds are irrelevant, and should be called once per minute.
     *
     * @param string|\DateTime $currentTime Relative calculation date
     *
     * @return bool Returns TRUE if the cron is due to run or FALSE if not
     */
    public function isDue($currentTime = 'now')
    {
        return $this->expr->isDue($currentTime);
    }
}
