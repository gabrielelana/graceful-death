<?php

class GracefulDeathBuilder
{
    private $main;
    private $afterViolentDeath;
    private $afterNaturalDeath;
    private $reanimationPolicy;
    private $options;

    public function __construct($main)
    {
        $this->main = $main;
        $this->afterViolentDeath = function($status) {};
        $this->afterNaturalDeath = function($status) {};
        $this->reanimationPolicy = GracefulDeath::DO_NOT_REANIMATE;
        $this->options = [
            'echoOutput' => true,
            'captureOutput' => true,
            'catchAndIgnoreSignals' => [],
        ];
    }

    public function afterViolentDeath($whatToDo)
    {
        $this->afterViolentDeath = $this->toClosure($whatToDo);
        return $this;
    }

    public function afterNaturalDeath($whatToDo)
    {
        $this->afterNaturalDeath = $this->toClosure($whatToDo);
        return $this;
    }

    public function sayGoodbyeToYourLovedOnce($whatToDo)
    {
        return $this->afterDeath($whatToDo);
    }

    public function afterDeath($whatToDo)
    {
        $this->afterViolentDeath = $this->toClosure($whatToDo);
        $this->afterNaturalDeath = $this->toClosure($whatToDo);
        return $this;
    }

    public function reanimationPolicy($policy)
    {
        $this->reanimationPolicy = $policy;
        return $this;
    }

    public function doNotCaptureOutput()
    {
        $this->options['captureOutput'] = false;
        return $this;
    }

    public function doNotEchoOutput()
    {
        $this->options['echoOutput'] = false;
        return $this;
    }

    public function catchAndIgnoreSignals($signals)
    {
        if (!is_array($signals)) $signals = [$signals];
        $signals = array_filter($signals, function($signal) {
            return is_integer($signal);
        });
        $this->options['catchAndIgnoreSignals'] = $signals;
        return $this;
    }

    public function run()
    {
        return (
            new GracefulDeath(
                $this->main,
                $this->afterNaturalDeath,
                $this->afterViolentDeath,
                $this->reanimationPolicy,
                $this->options
            )
        )->run();
    }

    private function toClosure($whatToDo)
    {
        if (is_integer($whatToDo)) {
            return function() use($whatToDo) { exit($whatToDo); };
        }
        if (is_string($whatToDo)) {
            return function() use($whatToDo) { echo $whatToDo; };
        }
        if (is_array($whatToDo) && count($whatToDo) === 2) {
            if (is_object($whatToDo[0])) {
                return $whatToDo;
            }
            list($status, $message) = $whatToDo;
            return function() use($status, $message) {
                echo $message;
                exit($status);
            };
        }
        if (is_callable($whatToDo)) {
            return $whatToDo;
        }
    }
}
