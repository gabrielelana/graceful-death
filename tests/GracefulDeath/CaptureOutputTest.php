<?php

class CaptureOutputTest extends GracefulDeathBaseTest
{
    public function testChildStandardOutputIsEchoedOnFatherStandardOutput()
    {
        $this->runFixture('printOutputOnStdout.php --what OUTPUT', function($stdout, $stderr) {
            $this->assertEquals('OUTPUT', $stdout);
            $this->assertEmpty($stderr);
        });
    }

    public function testChildStandardErrorIsEchoedOnFatherStandardError()
    {
        $this->runFixture('printOutputOnStderr.php --what OUTPUT', function($stdout, $stderr) {
            $this->assertEmpty($stdout);
            $this->assertEquals('OUTPUT', $stderr);
        });
    }

    public function testErrorsAreNotPrintToStderrWhenDisplayErrorsIsFalse()
    {
        $this->runFixture('doNotPrintErrorsByConfiguration.php', function($stdout, $stderr) {
            $this->assertEmpty($stdout);
            $this->assertEmpty($stderr);
        });
    }

    public function testErrorsAreStillLoggedWhenErrorLogIsEnabled()
    {
        $errorLogFilePath = tempnam(sys_get_temp_dir(), 'death');
        $this->runFixture("printOutputOnErrorLog.php --where {$errorLogFilePath}");
        $this->assertNotEmpty(file_get_contents($errorLogFilePath));
        $this->assertLinesAreFormattedAsAnErrorLog(file_get_contents($errorLogFilePath));
        @unlink($errorLogFilePath);
    }

    public function testCouldAvoidToPrintChildOutputWithOption()
    {
        $this->runFixture('doNotEchoOutput.php', function($stdout, $stderr) {
            $this->assertEmpty($stdout);
            $this->assertEmpty($stderr);
        });
    }

    public function testChildStandardOutputIsCapturedAndGivenToRetryPolicyForEvaluation()
    {
        GracefulDeath::around(function() {
            file_put_contents('php://stdout', 'OUTPUT');
            $this->raiseFatalError();
        })
        ->reanimationPolicy(function($status, $attempts, $stdout, $stderr) {
            $this->assertEquals('OUTPUT', $stdout);
            return false;
        })
        ->doNotEchoOutput()
        ->run();
    }

    public function testChildStandardErrorIsCapturedAndGivenToRetryPolicyForEvaluation()
    {
        GracefulDeath::around(function() {
            $this->raiseAndReportFatalError();
        })
        ->reanimationPolicy(function($status, $attempts, $stdout, $stderr) {
            $this->assertStringStartsWith('PHP Fatal error:', trim($stderr));
            return false;
        })
        ->doNotEchoOutput()
        ->run();
    }

    public function testChildStandardErrorIsCapturedAndAvailableAfterViolentDeath()
    {
        GracefulDeath::around(function() {
            $this->raiseAndReportFatalError();
        })
        ->afterViolentDeath(function($status, $stdout, $stderr) {
            $this->assertStringStartsWith('PHP Fatal error:', trim($stderr));
        })
        ->doNotEchoOutput()
        ->run();
    }

    public function testChildStandardOutputIsCapturedAndAvailableAfterViolentDeath()
    {
        GracefulDeath::around(function() {
            echo "This is a stdout test";
            $this->raiseAndReportFatalError();
        })
        ->afterViolentDeath(function($status, $stdout, $stderr) {
            $this->assertEquals("This is a stdout test", $stdout);
        })
        ->doNotEchoOutput()
        ->run();
    }

    public function testChildStandardOutputIsCapturedAfterNaturalDeath()
    {
        GracefulDeath::around(function() {
            file_put_contents('php://stdout', "This is a notice");
            $this->doSomethingUnharmful();
        })
        ->afterNaturalDeath(function($status, $stdout) {
            $this->assertEquals("This is a notice", $stdout);
        })
        ->doNotEchoOutput()
        ->run();
    }

    private function assertLinesAreFormattedAsAnErrorLog($string)
    {
        foreach (preg_split('/\s*\n\s*/', $string) as $line) {
            if (!empty($line)) {
                $this->assertStringMatchesFormat('[%s]%wPHP%s', $line);
            }
        }
    }
}
