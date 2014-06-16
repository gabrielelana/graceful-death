<?php

class AvoidFutileMedicalCareTest extends GracefulDeathBaseTest
{
    public function testTooManyViolentDeath()
    {
        $numberOfFailures = 12;
        $inAmountOfTime = 60;
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->liveForever()
        ->avoidFutileMedicalCare($numberOfFailures, $inAmountOfTime)
        ->reanimationPolicy($this->willBeCalled($this->exactly($numberOfFailures - 1)))
        ->afterNaturalDeath($this->willBeCalled($this->never()))
        ->afterViolentDeath($this->willBeCalled($this->once()))
        ->run();
    }
}
