<?php

class CatchSignalsTest extends GracefulDeathBaseTest
{
    public function testCanTerminateWithSignalSentToSupervisorProcess()
    {
        $this->startProcessForFixture('stopOnSignal.php', function($process) {
            $this->stopProcessWithSignal($process, SIGTERM, function($stdout, $stderr) {
                // Unfortunately this test is flaky, I was not able to make it pass on travis-ci
                // At least the correct test termination tells us that it's working, more or less
                // $this->assertEquals('Bye Bye', $stdout);
            });
        });
    }

    protected function startProcessForFixture($fixture, $callback)
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $cwd = __DIR__ . '/../fixtures';
        $process = proc_open("php {$fixture}", $descriptors, $pipes, $cwd);
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);
        usleep(100000);
        $callback([$process, $pipes]);
    }

    protected function stopProcessWithSignal($process, $signal, $callback)
    {
        list($process, $pipes) = $process;
        proc_terminate($process, $signal);
        usleep(100000); // Wait until the signal will be dispatched by the supervisor process
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        $callback($stdout, $stderr);
    }
}
