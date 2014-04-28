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
                $exitStatusOfLastChild = pcntl_wexitstatus($status);
                /* $this->lastWill->stop(); */
                /* $this->lastWill->recordedOnStandardOutput(); */
                /* $this->lastWill->recordedOnStandardError(); */
                /* $this->lastWill->replay(); */
                /* $lastWill->stop(); */
                $lastWill->stop();
                /* $outputPrintedByLastChild = $lastWill->outputPrintedByLastChild(); */
                return $this->afterChildDeathWithStatus($exitStatusOfLastChild, $lifeCounter, $lastWill);
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
                    $STDOUT = fopen($lastWill->stdoutFilePath, 'wb+');
                }
                /* $this->lastWill->record(); */
                call_user_func($this->main, $lifeCounter);
                exit(0);
            }
        }
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
                $status, $lifeCounter, $lastWill->whatDidHeSayOnStdout()
            );
        }
        return $this->reanimationPolicy >= $lifeCounter;
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
