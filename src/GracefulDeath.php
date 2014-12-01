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
        while(true) {
            $attempts += 1;
            $lastWill = new LastWill($this->options);
            $pid = pcntl_fork();
            if ($pid >= 0) {
                if ($pid) {
                    $this->catchAndForwardSignalTo($pid);
                    $status = $this->waitUntilProcessDiesAndReturnStatus($pid);
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
            return [
                false, 
                call_user_func(
                    $this->afterViolentDeath, $status, 
                    $lastWill->whatDidHeSayOnStdout(),
                    $lastWill->whatDidHeSayOnStderr()
                )];
        }
        return [
            false, 
            call_user_func(
                $this->afterNaturalDeath, $status,
                $lastWill->whatDidHeSayOnStdout()
            )];
    }

    private function canTryAnotherTime($status, $attempts, $lastWill)
    {
        return call_user_func($this->reanimationPolicy,
            $status, $attempts,
            $lastWill->whatDidHeSayOnStdout(),
            $lastWill->whatDidHeSayOnStderr()
        );
    }

    private function catchAndForwardSignalTo($pid)
    {
        foreach ($this->options['catchSignals'] as $signal) {
            pcntl_signal($signal, function($signal) use ($pid) {
                posix_kill($pid, $signal);
            });
        }
    }

    private function waitUntilProcessDiesAndReturnStatus($pid)
    {
        if (!empty($this->options['catchSignals'])) {
            while (pcntl_waitpid($pid, $status, WNOHANG) <= 0) {
                pcntl_signal_dispatch();
                usleep(100000);
            }
            return $status;
        }
        pcntl_waitpid($pid, $status);
        return $status;
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
