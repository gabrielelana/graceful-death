<?php

class GracefulDeath
{
    private $main;
    private $afterNaturalDeath;
    private $afterViolentDeath;
    private $reanimationPolicy;
    private $options;

    const DO_NOT_REANIMATE = 0;
    const GIVE_ME_ANOTHER_CHACE = 1;

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
        $stdoutFilePath = tempnam(sys_get_temp_dir(), 'death');
        $pid = pcntl_fork();
        if ($pid >= 0) {
            if ($pid) {
                pcntl_waitpid($pid, $status);
                $exitStatusOfLastChild = pcntl_wexitstatus($status);
                $outputPrintedByLastChild = $this->outputPrintedByLastChild($stdoutFilePath);
                return $this->afterChildDeathWithStatus(
                    $exitStatusOfLastChild, $lifeCounter, $outputPrintedByLastChild
                );
            } else {
                // If you are thinking that this is an hack of an hack you are right...
                // The fact is that what works for STDOUT doesn't work for STDERR...
                // We are forced to merge the STDERR to the STDOUT of the child process
                // to be able to capture it from the parent process. Sadly we loose the
                // distinction between the two
                if ($this->options['captureOutput']) {
                    fclose(STDOUT);
                    if ($this->options['redirectStandardError']) {
                        fclose(STDERR);
                        ini_set('display_errors', 'stdout');
                    }
                    $STDOUT = fopen($stdoutFilePath, 'wb+');
                }
                call_user_func($this->main, $lifeCounter);
                exit(0);
            }
        }
    }

    private function afterChildDeathWithStatus($status, $lifeCounter, $output)
    {
        if ($this->options['echoOutput']) {
            echo $output;
        }
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

    private function outputPrintedByLastChild($stdoutFilePath)
    {
        $output = file_get_contents($stdoutFilePath);
        @unlink($stdoutFilePath);
        return $output;
    }
}
