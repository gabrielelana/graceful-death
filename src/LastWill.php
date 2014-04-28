<?php

class LastWill
{
    private $options;
    private $capturedFromStdout;
    private $capturedFromStderr;

    public function __construct($options)
    {
        $this->options = $options;
        $this->stdoutFilePath = tempnam(sys_get_temp_dir(), 'death');
        $this->stderrFilePath = tempnam(sys_get_temp_dir(), 'death');
        $this->capturedFromStdout = '';
        $this->capturedFromStderr = '';
    }

    public function capture()
    {
        // If you are thinking that this is an hack of an hack you are right...
        // The fact is that what works for STDOUT doesn't work for STDERR...
        // We are forced to merge the STDERR to the STDOUT of the child process
        // to be able to capture it from the parent process. Sadly we loose the
        // distinction between the two
        global $STDOUT;
        if ($this->options['captureOutput']) {
            fclose(STDOUT);
            if ($this->options['redirectStandardError']) {
                fclose(STDERR);
                ini_set('display_errors', 'stdout');
            }
            $STDOUT = fopen($this->stdoutFilePath, 'wb+');
        }
    }

    public function stop()
    {
        $this->capturedFromStdout = $this->contentOf($this->stdoutFilePath);
    }

    public function play()
    {
        if ($this->options['echoOutput']) {
            echo $this->capturedFromStdout;
        }
    }

    public function whatDidHeSayOnStdout()
    {
        return $this->capturedFromStdout;
    }

    private function contentOf()
    {
        $output = file_get_contents($this->stdoutFilePath);
        @unlink($this->stdoutFilePath);
        return $output;
    }
}
