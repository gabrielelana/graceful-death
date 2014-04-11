<?php


class GracefulDeathTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCatchViolentDeath()
    {
        $result = GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->afterViolentDeath(function($status) {
            return 'Catched';
        })
        ->run();

        $this->assertEquals('Catched', $result);
    }

    public function testCanCatchNaturalDeath()
    {
        $result = GracefulDeath::around(function() {
            $this->doSomethingUnharmful();
        })
        ->afterNaturalDeath(function($status) {
            return 'Catched';
        })
        ->run();

        $this->assertEquals('Catched', $result);
    }

    public function testNaturalDeathIsDeath()
    {
        $result = GracefulDeath::around(function() {
            $this->doSomethingUnharmful();
        })
        ->afterDeath(function($status) {
            return 'Catched';
        })
        ->run();

        $this->assertEquals('Catched', $result);
    }

    public function testViolentDeathIsDeath()
    {
        $result = GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->afterDeath(function($status) {
            return 'Catched';
        })
        ->run();

        $this->assertEquals('Catched', $result);
    }

    public function testCanBeReanimatedOneTime()
    {
        $result = GracefulDeath::around(function($lifeCounter) {
            if ($lifeCounter === 1) {
                $this->raiseFatalError();
            } else {
                $this->doSomethingUnharmful();
            }
        })
        ->reanimationPolicy(GracefulDeath::GIVE_ME_ANOTHER_CHACE)
        ->afterViolentDeath(function($status) {
            return 'Violent';
        })
        ->afterNaturalDeath(function($status) {
            return 'Natural';
        })
        ->run();

        $this->assertEquals('Natural', $result);
    }

    public function testCanBeReanimatedMoreThanOneTime()
    {
        $numberOfRetry = 4;
        $result = GracefulDeath::around(function($lifeCounter) use($numberOfRetry) {
            if ($lifeCounter < $numberOfRetry) {
                $this->raiseFatalError();
            } else {
                $this->doSomethingUnharmful();
            }
        })
        ->reanimationPolicy($numberOfRetry)
        ->afterViolentDeath(function($status) {
            return 'Violent';
        })
        ->afterNaturalDeath(function($status) {
            return 'Natural';
        })
        ->run();

        $this->assertEquals('Natural', $result);
    }

    public function testCanBeReanimatedWithArbitraryPolicy()
    {
        $result = GracefulDeath::around(function($lifeCounter) {
            if ($lifeCounter < 3) {
                exit(5);
            } else {
                $this->raiseFatalError();
            }
        })
        ->reanimationPolicy(function($status, $lifeCounter, $output) {
            return $status === 5;
        })
        ->afterViolentDeath(function($status) {
            return 'Violent';
        })
        ->afterNaturalDeath(function($status) {
            return 'Natural';
        })
        ->run();

        $this->assertEquals('Violent', $result);
    }

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



    private function doSomethingUnharmful()
    {
        return 1 + 1;
    }

    private function raiseFatalError($doNotReportErrors = true)
    {
        if ($doNotReportErrors) {
            error_reporting(E_ALL ^ E_ERROR);
        }
        // Instance an unknown class cause a fatal error
        new UnknownClass();
    }
}
