<?php

namespace GracefulDeath;

class LastWill
{
    private $options;
    private $stdoutFilePath;
    private $stderrFilePath;
    private $capturedFromStdout;
    private $capturedFromStderr;
    private $userSettings;
    private $userSettingsToSave;

    public function __construct($options)
    {
        $this->options = $options;
        $this->stdoutFilePath = tempnam(sys_get_temp_dir(), 'death');
        $this->stderrFilePath = tempnam(sys_get_temp_dir(), 'death');
        $this->capturedFromStdout = '';
        $this->capturedFromStderr = '';
        $this->userSettings = [];
        $this->userSettingsToSave = ['error_log', 'log_errors', 'display_errors'];
        $this->saveUserSettings();
    }

    public function capture()
    {
        if (!$this->options['captureOutput']) return;
        $this->redirectStdout();
        $this->redirectStderr();
    }

    private function removeErrorLogHeaderFromEachLine($content)
    {
        return preg_replace('/^\[[^\]]+\]\s(.*)$/m', '\1', $content);
    }

    public function stop()
    {
        $this->capturedFromStdout = $this->contentOf($this->stdoutFilePath);
        $this->capturedFromStderr = $this->removeErrorLogHeaderFromEachLine(
            $this->contentOf($this->stderrFilePath)
        );
    }

    public function play()
    {
        if (!$this->options['echoOutput']) return;
        $this->playCapturedStdoutOnStdout();
        $this->playCapturedStderrOnStderr();
        $this->playCapturedStderrOnErrorLog();
    }

    private function playCapturedStdoutOnStdout()
    {
        file_put_contents('php://stdout', $this->capturedFromStdout);
    }

    private function playCapturedStderrOnStderr()
    {
        if ($this->userSettings['display_errors']) {
            file_put_contents('php://stderr', $this->capturedFromStderr);
        }
    }

    private function playCapturedStderrOnErrorLog()
    {
        if ($this->userSettings['log_errors'] && $this->userSettings['error_log']) {
            $handle = fopen($this->userSettings['error_log'], 'a');
            if ($handle) {
                fwrite($handle, $this->capturedFromStderr);
                fclose($handle);
            }
        }
    }

    public function whatDidHeSayOnStdout()
    {
        return $this->capturedFromStdout;
    }

    public function whatDidHeSayOnStderr()
    {
        return $this->capturedFromStderr;
    }

    private function contentOf($filePath)
    {
        $content = file_get_contents($filePath);
        @unlink($filePath);
        return $content;
    }

    private function saveUserSettings()
    {
        foreach($this->userSettingsToSave as $key) {
            $this->userSettings[$key] = ini_get($key);
        }
    }

    private function redirectStdout()
    {
        global $STDOUT;
        fclose(STDOUT);
        $STDOUT = fopen($this->stdoutFilePath, 'wb+');
    }

    private function redirectStderr()
    {
        ini_set('error_log', $this->stderrFilePath);
        ini_set('log_errors', 1);
        ini_set('display_errors', 0);
    }
}
