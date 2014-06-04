<?php

class AvoidFutileMedicalCareTest extends GracefulDeathBaseTest
{
    public function testTooManyViolentDeath()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->liveForever()
        ->avoidFutileMedicalCare()
        ->afterNaturalDeath($this->willBeCalled($this->never()))
        ->afterViolentDeath($this->willBeCalled($this->once()))
        ->run();
    }
}
