<?php

class LastWill
{
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
        $this->stdoutFilePath = tempnam(sys_get_temp_dir(), 'death');
        $this->stderrFilePath = tempnam(sys_get_temp_dir(), 'death');
    }
}
