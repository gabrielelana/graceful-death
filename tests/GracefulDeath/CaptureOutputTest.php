<?php

class CaptureOutpuTest extends GracefulDeathBaseTest
{
    public function testChildStandardOutputIsCapturedAndGivenToRetryPolicyForEvaluation()
    {
        ob_start();
        GracefulDeath::around(function() {
            echo 'OUTPUT';
            $this->raiseFatalError();
        })
        ->reanimationPolicy(function($status, $lifeCounter, $output) {
            $this->assertEquals('OUTPUT', $output);
            return false;
        })
        ->run();
        ob_end_clean();
    }

    public function testChildStandardOutputIsEchoedOnFatherStandardOutput()
    {
        ob_start();
        GracefulDeath::around(function() {
            echo 'OUTPUT';
            $this->raiseFatalError();
        })
        ->run();
        $outputPrintedFromParent = ob_get_clean();

        $this->assertEquals('OUTPUT', $outputPrintedFromParent);
    }

    public function testChildStandardErrorIsCapturedAndGivenToRetryPolicyForEvaluation()
    {
        ob_start();
        GracefulDeath::around(function() {
            $this->raiseFatalError($doNotReportErrors = false);
        })
        ->reanimationPolicy(function($status, $lifeCounter, $output) {
            $this->assertStringStartsWith('Fatal error:', trim($output));
            return false;
        })
        ->run();
        ob_end_clean();
    }
}
