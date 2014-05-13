<?php

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
        return new GracefulDeathBuilder($main);
    }

    public function __construct($main, $afterNaturalDeath, $afterViolentDeath, $reanimationPolicy, $options)
    {
        $this->main = $main;
        $this->afterNaturalDeath = $afterNaturalDeath;
        $this->afterViolentDeath = $afterViolentDeath;
        $this->reanimationPolicy = $reanimationPolicy;
        $this->options = $options;
    }

    public function run($lifeCounter = 1)
    {
        $this->catchAndIgnoreSignals();
        $lastWill = new LastWill($this->options);
        $pid = pcntl_fork();
        if ($pid >= 0) {
            if ($pid) {
                pcntl_waitpid($pid, $status);
                $lastWill->stop();
                return $this->afterChildDeathWithStatus(
                    $this->exitStatusOfLastChild($status), $lifeCounter, $lastWill
                );
            } else {
                $lastWill->capture();
                call_user_func($this->main, $lifeCounter);
                exit(0);
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

    private function afterChildDeathWithStatus($status, $lifeCounter, $lastWill)
    {
        $lastWill->play();
        if ($status !== 0) {
            if ($this->canTryAnotherTime($status, $lifeCounter, $lastWill)) {
                return $this->run($lifeCounter + 1);
            }
            return call_user_func($this->afterViolentDeath, $status);
        }
        return call_user_func($this->afterNaturalDeath, $status);
    }

    private function canTryAnotherTime($status, $lifeCounter, $lastWill)
    {
        if (is_callable($this->reanimationPolicy)) {
            return call_user_func($this->reanimationPolicy,
                $status, $lifeCounter,
                $lastWill->whatDidHeSayOnStdout(),
                $lastWill->whatDidHeSayOnStderr()
            );
        }
        if (is_numeric($this->reanimationPolicy)) {
            return $this->reanimationPolicy >= $lifeCounter;
        }
        return (bool) $this->reanimationPolicy;
    }

    private function catchAndIgnoreSignals()
    {
        foreach ($this->options['catchAndIgnoreSignals'] as $signal) {
            pcntl_signal($signal, function($signal) {
                // catch but do nothing
            });
        }
    }
}
