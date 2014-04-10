<?php

class GracefulDeath
{
    private $main;
    private $afterNaturalDeath;
    private $afterViolentDeath;
    private $reanimationPolicy;

    const DO_NOT_REANIMATE = 0;
    const GIVE_ME_ANOTHER_CHACE = 1;
    const I_WILL_LIVE_FOREVER = -1;

    public static function around($main)
    {
        return new GracefulDeathBuilder($main);
    }

    public function __construct($main, $afterNaturalDeath, $afterViolentDeath, $reanimationPolicy)
    {
        $this->main = $main;
        $this->afterNaturalDeath = $afterNaturalDeath;
        $this->afterViolentDeath = $afterViolentDeath;
        $this->reanimationPolicy = $reanimationPolicy;
    }

    public function run()
    {
        $pid = pcntl_fork();
        if ($pid >= 0) {
            if ($pid) {
                pcntl_waitpid($pid, $status);
                return $this->afterChildDeathWithStatus(pcntl_wexitstatus($status));
            } else {
                call_user_func($this->main);
                exit(0);
            }
        }
    }

    private function afterChildDeathWithStatus($status)
    {
        if ($status !== 0) {
            return call_user_func($this->afterViolentDeath, $status);
        }
        return call_user_func($this->afterNaturalDeath, $status);
    }
}
