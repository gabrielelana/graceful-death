<?php

use GracefulDeath\Builder;
use GracefulDeath\LastWill;
use GracefulDeath\Life;

class GracefulDeath
{
    private $main;
    private $afterNaturalDeath;
    private $afterViolentDeath;
    private $reanimationPolicy;
    private $options;

    const DO_NOT_REANIMATE = 0;
    const GIVE_ME_ANOTHER_CHANCE = 1;

    public static function around($main)
    {
        return new Builder($main);
    }

    public function __construct($main, $afterNaturalDeath, $afterViolentDeath, $reanimationPolicy, $options)
    {
        $this->main = $main;
        $this->afterNaturalDeath = $afterNaturalDeath;
        $this->afterViolentDeath = $afterViolentDeath;
        $this->reanimationPolicy = $reanimationPolicy;
        $this->options = $options;
    }

    public function run()
    {
        $attempts = 0;
        $this->catchAndIgnoreSignals();
        while(true) {
            $attempts += 1;
            $lastWill = new LastWill($this->options);
            $pid = pcntl_fork();
            if ($pid >= 0) {
                if ($pid) {
                    pcntl_waitpid($pid, $status);
                    $lastWill->stop();
                    list($tryAnotherTime, $result) =
                        $this->afterChildDeathWithStatus(
                            $this->exitStatusOfLastChild($status), $attempts, $lastWill
                        );
                    if (!$tryAnotherTime) {
                        return $result;
                    }
                } else {
                    $life = new Life($attempts);
                    $this->catchSignalsFor($life);
                    $lastWill->capture();
                    return call_user_func($this->main, $life);
                }
            }
        }
    }

    private function exitStatusOfLastChild($status)
    {
        $exitStatusOfLastChild = pcntl_wexitstatus($status);
        $lastChildExitedNormally = pcntl_wifexited($status);
        if (($exitStatusOfLastChild === 0) && !$lastChildExitedNormally) {
            $exitStatusOfLastChild = 1;
        }
        return $exitStatusOfLastChild;
    }

    private function afterChildDeathWithStatus($status, $attempts, $lastWill)
    {
        $lastWill->play();
        if ($status !== 0) {
            if ($this->canTryAnotherTime($status, $attempts, $lastWill)) {
                return [true, null];
            }
            return [false, call_user_func($this->afterViolentDeath, $status)];
        }
        return [false, call_user_func($this->afterNaturalDeath, $status)];
    }

    private function canTryAnotherTime($status, $attempts, $lastWill)
    {
        if (is_callable($this->reanimationPolicy)) {
            return call_user_func($this->reanimationPolicy,
                $status, $attempts,
                $lastWill->whatDidHeSayOnStdout(),
                $lastWill->whatDidHeSayOnStderr()
            );
        }
        if (is_numeric($this->reanimationPolicy)) {
            return $this->reanimationPolicy >= $attempts;
        }
        if (is_bool($this->reanimationPolicy)) {
            return $this->reanimationPolicy;
        }
        return false;
    }

    private function catchAndIgnoreSignals()
    {
        foreach ($this->options['catchSignals'] as $signal) {
            pcntl_signal($signal, function($signal) {
                // catch but do nothing
            });
        }
    }

    private function catchSignalsFor($life)
    {
        foreach ($this->options['catchSignals'] as $signal) {
            pcntl_signal($signal, function($signal) use($life) {
                $life->sendSignal($signal);
            });
        }
    }
}
