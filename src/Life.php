<?php

class Life
{
    private $counter;
    private $signal;

    public function __construct($counter)
    {
        $this->counter = $counter;
        $this->signal = null;
    }

    public function askedToStop()
    {
        // This needs to be called to dispatch the signals received by the current
        // process. The PHP process doesn't do that by default, if you have some piece
        // of code that it's executed periodically then this is the best method
        pcntl_signal_dispatch();
        return !is_null($this->signal);
    }

    public function signal()
    {
        return $this->signal;
    }

    public function sendSignal($signal)
    {
        $this->signal = $signal;
    }

    public function numberOfPreviousLives()
    {
        return $this->counter;
    }
}
