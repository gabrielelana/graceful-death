<?php

class GracefulDeath
{
    private $main;
    private $afterNaturalDeath;
    private $afterViolentDeath;
    private $reanimationPolicy;

    const DO_NOT_REANIMATE = 0;
    const GIVE_ME_ANOTHER_CHACE = 1;

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

    public function run($lifeCounter = 1)
    {
        $stdoutFilePath = tempnam(sys_get_temp_dir(), 'death');
        $pid = pcntl_fork();
        if ($pid >= 0) {
            if ($pid) {
                pcntl_waitpid($pid, $status);
                $exitStatusOfLastChild = pcntl_wexitstatus($status);
                $outputPrintedByLastChild = file_get_contents($stdoutFilePath);
                return $this->afterChildDeathWithStatus(
                    $exitStatusOfLastChild, $lifeCounter, $outputPrintedByLastChild
                );
            } else {
                // If you are thinking that this is an hack of an hack you are right...
                // The fact is that what works for STDOUT doesn't work for STDERR...
                // We are forced to merge the STDERR to the STDOUT of the child process
                // to be able to capture it from the parent process. Sadly we loose the
                // distinction between the two
                fclose(STDOUT);
                fclose(STDERR);
                ini_set('display_errors', 'stdout');
                $STDOUT = fopen($stdoutFilePath, 'wb+');
                call_user_func($this->main, $lifeCounter);
                exit(0);
            }
        }
    }

    private function afterChildDeathWithStatus($status, $lifeCounter, $output)
    {
        echo $output;
        if ($status !== 0) {
            if ($this->canTryAnotherTime($status, $lifeCounter, $output)) {
                return $this->run($lifeCounter + 1);
            }
            return call_user_func($this->afterViolentDeath, $status);
        }
        return call_user_func($this->afterNaturalDeath, $status);
    }

    private function canTryAnotherTime($status, $lifeCounter, $output)
    {
        if (is_callable($this->reanimationPolicy)) {
            return call_user_func($this->reanimationPolicy, $status, $lifeCounter, $output);
        }
        return $this->reanimationPolicy >= $lifeCounter;
    }
}
