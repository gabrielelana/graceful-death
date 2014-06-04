<?php

namespace GracefulDeath;

use GracefulDeath;

class Builder
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
        $this->reanimationPolicy = function() {return false;};
        $this->options = [
            'echoOutput' => true,
            'captureOutput' => true,
            'catchSignals' => [],
        ];
    }

    public function afterViolentDeath($whatToDo)
    {
        $this->afterViolentDeath = $this->toLastAct($whatToDo);
        return $this;
    }

    public function afterNaturalDeath($whatToDo)
    {
        $this->afterNaturalDeath = $this->toLastAct($whatToDo);
        return $this;
    }

    public function sayGoodbyeToYourLovedOnce($whatToDo)
    {
        return $this->afterDeath($whatToDo);
    }

    public function afterDeath($whatToDo)
    {
        $this->afterViolentDeath = $this->toLastAct($whatToDo);
        $this->afterNaturalDeath = $this->toLastAct($whatToDo);
        return $this;
    }

    public function reanimationPolicy($policy)
    {
        $this->reanimationPolicy = $this->toReanimationPolicy($policy);
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

    public function catchSignals($signals)
    {
        if (!is_array($signals)) $signals = [$signals];
        $signals = array_filter($signals, function($signal) {
            return is_integer($signal);
        });
        $this->options['catchSignals'] = $signals;
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

    private function toReanimationPolicy($policy)
    {
        if (is_integer($policy)) {
            return function($status, $attempts, $stdout, $stderr) use ($policy) {
                return $policy >= $attempts;
            };
        }
        if (is_bool($policy)) {
            return function($status, $attempts, $stdout, $stderr) use ($policy) {
                return $policy;
            };
        }
        return $policy;
    }

    private function toLastAct($whatToDo)
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
