<?php

abstract class GracefulDeathBaseTest extends \PHPUnit_Framework_TestCase
{
    protected function doSomethingUnharmful()
    {
        return 1 + 1;
    }

    protected function raiseFatalError()
    {
        error_reporting(E_ALL ^ E_ERROR);
        // Instance an unknown class cause a fatal error
        new UnknownClass();
    }

    protected function raiseAndReportFatalError()
    {
        // Instance an unknown class cause a fatal error
        new UnknownClass();
    }

    protected function willBeCalled($howManyTimes) {
        $mock = $this->getMock('stdClass', array('aCallback'));
        $mock->expects($howManyTimes)->method('aCallback')->will($this->returnValue(true));
        return [$mock, 'aCallback'];
    }

    protected function runFixture($fixture, $callback)
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $cwd = __DIR__ . '/../fixtures';
        $process = proc_open("php {$fixture}", $descriptors, $pipes, $cwd);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $callback($stdout, $stderr);
    }
}
