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
